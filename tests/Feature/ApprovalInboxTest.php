<?php

namespace Tests\Feature;

use App\Models\ApprovalDelegation;
use App\Models\ApprovalRequest;
use App\Models\ApprovalRequestAction;
use App\Models\ApprovalRequestStep;
use App\Models\ApprovalWorkflow;
use App\Models\Base;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeClass;
use App\Models\EmploymentStatus;
use App\Models\Position;
use App\Models\Section;
use App\Models\Unit;
use App\Models\User;
use App\Services\ApprovalRequestActionService;
use App\Services\ApprovalRequestSubmissionService;
use App\Support\ApprovalDemoApprovable;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ApprovalInboxTest extends TestCase
{
    use RefreshDatabase;

    public function test_inbox_requires_authentication_and_active_mapping(): void
    {
        $this->get(route('approval-inbox.index'))->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create())->get(route('approval-inbox.index'))
            ->assertOk()->assertSee('must be mapped to an active employee');

        [$employee, $user] = $this->mappedEmployee('INACTIVE');
        $employee->update(['is_active' => false]);
        $this->actingAs($user)->get(route('approval-inbox.index'))
            ->assertOk()->assertSee('must be mapped to an active employee');
    }

    public function test_inbox_only_shows_current_canonical_approver(): void
    {
        [$requester, $requesterUser] = $this->mappedEmployee('REQUESTER');
        [$current, $currentUser] = $this->mappedEmployee('CURRENT', true);
        [$future] = $this->mappedEmployee('FUTURE', true);
        $this->workflow();
        $request = $this->submit($requester, $requesterUser, [$current->id, $future->id]);

        $this->actingAs($currentUser)->get(route('approval-inbox.index'))
            ->assertOk()->assertSee('#'.$request->id)->assertSee('CURRENT')
            ->assertDontSee('FUTURE Position');
        $this->actingAs($this->mapUser($future))->get(route('approval-inbox.index'))
            ->assertOk()->assertDontSee('#'.$request->id);
    }

    public function test_valid_delegate_is_visible_but_wrong_department_and_revoked_delegate_are_not(): void
    {
        [$requester, $requesterUser] = $this->mappedEmployee('DELEGATE-REQUESTER');
        [$canonical] = $this->mappedEmployee('DELEGATE-CANONICAL', true);
        [$delegate, $delegateUser] = $this->mappedEmployee('DELEGATE-ACTOR', true);
        $this->workflow();
        $request = $this->submit($requester, $requesterUser, [$canonical->id]);
        $dates = [Carbon::now()->subDay()->toDateString(), Carbon::now()->addDay()->toDateString()];

        $delegation = ApprovalDelegation::create([
            'acting_for_employee_id' => $canonical->id,
            'delegate_employee_id' => $delegate->id,
            'effective_from' => $dates[0],
            'effective_until' => $dates[1],
            'reason' => 'Coverage',
            'scope_type' => ApprovalDelegation::SCOPE_ALL,
            'status' => ApprovalDelegation::STATUS_ACTIVE,
        ]);

        $this->actingAs($delegateUser)->get(route('approval-inbox.index'))
            ->assertOk()->assertSee('#'.$request->id)->assertSee('Acting for:');

        $delegation->update(['status' => ApprovalDelegation::STATUS_REVOKED, 'revoked_at' => now()]);
        $this->actingAs($delegateUser)->get(route('approval-inbox.index'))
            ->assertOk()->assertDontSee('#'.$request->id);

        $delegation->update(['status' => ApprovalDelegation::STATUS_ACTIVE, 'revoked_at' => null, 'effective_until' => Carbon::yesterday()->toDateString()]);
        $this->actingAs($delegateUser)->get(route('approval-inbox.index'))
            ->assertOk()->assertDontSee('#'.$request->id);
    }

    public function test_detail_access_supports_requester_current_and_previous_actor_but_not_unrelated_employee(): void
    {
        [$requester, $requesterUser] = $this->mappedEmployee('DETAIL-REQUESTER');
        [$current, $currentUser] = $this->mappedEmployee('DETAIL-CURRENT', true);
        [$previous, $previousUser] = $this->mappedEmployee('DETAIL-PREVIOUS', true);
        [$unrelated, $unrelatedUser] = $this->mappedEmployee('DETAIL-UNRELATED', true);
        $this->workflow(min: 2, max: 2);
        $request = $this->submit($requester, $requesterUser, [$previous->id, $current->id]);
        app(ApprovalRequestActionService::class)->approve($request, $previousUser, 'previous-detail', 'Previous review completed');

        $this->actingAs($requesterUser)->get(route('approval-inbox.show', $request))->assertOk()->assertSee('Action history');
        $this->actingAs($currentUser)->get(route('approval-inbox.show', $request))->assertOk()->assertSee('Approve');
        $this->actingAs($previousUser)->get(route('approval-inbox.show', $request))->assertOk()->assertSee('Previous review completed');
        $this->actingAs($unrelatedUser)->get(route('approval-inbox.show', $request))->assertForbidden();
    }

    public function test_canonical_and_delegated_approval_use_runtime_service_and_activate_next_step(): void
    {
        [$requester, $requesterUser] = $this->mappedEmployee('ACTION-REQUESTER');
        [$first, $firstUser] = $this->mappedEmployee('ACTION-FIRST', true);
        [$second, $secondUser] = $this->mappedEmployee('ACTION-SECOND', true);
        [$delegate, $delegateUser] = $this->mappedEmployee('ACTION-DELEGATE', true);
        $this->workflow(min: 2, max: 2);
        $request = $this->submit($requester, $requesterUser, [$first->id, $second->id]);

        $this->actingAs($firstUser)->post(route('approval-inbox.approve', $request), [
            'idempotency_key' => (string) Str::uuid(), 'remarks' => 'Reviewed',
        ])->assertRedirect(route('approval-inbox.show', $request));
        $this->assertSame(2, $request->fresh()->current_step_order);

        $dates = [Carbon::now()->subDay()->toDateString(), Carbon::now()->addDay()->toDateString()];
        $delegation = ApprovalDelegation::create([
            'acting_for_employee_id' => $second->id,
            'delegate_employee_id' => $delegate->id,
            'effective_from' => $dates[0], 'effective_until' => $dates[1],
            'reason' => 'Coverage', 'scope_type' => ApprovalDelegation::SCOPE_ALL,
            'status' => ApprovalDelegation::STATUS_ACTIVE,
        ]);
        $this->actingAs($delegateUser)->post(route('approval-inbox.approve', $request), [
            'idempotency_key' => (string) Str::uuid(), 'remarks' => 'Delegated review',
            'actor_employee_id' => $requester->id,
            'canonical_approver_employee_id' => $requester->id,
        ])->assertRedirect();

        $action = ApprovalRequestAction::query()->where('action', ApprovalRequestAction::ACTION_APPROVE)->latest('id')->firstOrFail();
        $this->assertSame($delegate->id, $action->actor_employee_id);
        $this->assertSame($second->id, $action->canonical_approver_employee_id);
        $this->assertSame($second->id, $action->acting_for_employee_id);
        $this->assertSame($delegation->id, $action->approval_delegation_id);
    }

    public function test_future_approver_requester_and_terminal_request_cannot_approve(): void
    {
        [$requester, $requesterUser] = $this->mappedEmployee('SECURITY-REQUESTER');
        [$first, $firstUser] = $this->mappedEmployee('SECURITY-FIRST', true);
        [$future, $futureUser] = $this->mappedEmployee('SECURITY-FUTURE', true);
        $this->workflow(min: 2, max: 2);
        $request = $this->submit($requester, $requesterUser, [$first->id, $future->id]);

        $this->actingAs($futureUser)->post(route('approval-inbox.approve', $request), ['idempotency_key' => (string) Str::uuid()])->assertSessionHasErrors('actor');
        $this->actingAs($requesterUser)->post(route('approval-inbox.approve', $request), ['idempotency_key' => (string) Str::uuid()])->assertSessionHasErrors('actor');
        app(ApprovalRequestActionService::class)->approve($request, $firstUser, 'terminal-security');
        app(ApprovalRequestActionService::class)->approve($request, $futureUser, 'terminal-security-2');
        $this->assertSame(ApprovalRequest::STATUS_APPROVED, $request->fresh()->status);
        $this->actingAs($futureUser)->get(route('approval-inbox.index'))
            ->assertOk()->assertDontSee('#'.$request->id);
        $this->actingAs($requesterUser)->get(route('approval-inbox.show', $request))
            ->assertOk()->assertDontSee('Current approval context')->assertDontSee('Requester actions');
    }

    public function test_reject_cancels_future_steps_and_renders_history(): void
    {
        [$requester, $requesterUser] = $this->mappedEmployee('REJECT-REQUESTER');
        [$current, $currentUser] = $this->mappedEmployee('REJECT-CURRENT', true);
        [$future] = $this->mappedEmployee('REJECT-FUTURE', true);
        $this->workflow(min: 2, max: 2);
        $request = $this->submit($requester, $requesterUser, [$current->id, $future->id]);

        $this->actingAs($currentUser)->post(route('approval-inbox.reject', $request), [
            'idempotency_key' => (string) Str::uuid(), 'remarks' => 'Needs correction',
        ])->assertRedirect();
        $request->refresh();
        $this->assertSame(ApprovalRequest::STATUS_REJECTED, $request->status);
        $this->assertSame(ApprovalRequestStep::STATUS_CANCELLED, $request->steps()->where('step_order', 2)->value('status'));
        $this->actingAs($requesterUser)->get(route('approval-inbox.show', $request))->assertOk()->assertSee('Needs correction');
    }

    public function test_requester_can_cancel_pending_request_after_previous_approval_but_non_requester_cannot(): void
    {
        [$requester, $requesterUser] = $this->mappedEmployee('CANCEL-REQUESTER');
        [$first, $firstUser] = $this->mappedEmployee('CANCEL-FIRST', true);
        [$second] = $this->mappedEmployee('CANCEL-SECOND', true);
        $this->workflow(min: 2, max: 2);
        $request = $this->submit($requester, $requesterUser, [$first->id, $second->id]);
        app(ApprovalRequestActionService::class)->approve($request, $firstUser, 'cancel-previous');

        $this->actingAs($firstUser)->post(route('approval-requests.cancel', $request), [
            'idempotency_key' => (string) Str::uuid(),
        ])->assertSessionHasErrors('actor');
        $this->actingAs($requesterUser)->post(route('approval-requests.cancel', $request), [
            'idempotency_key' => (string) Str::uuid(), 'remarks' => 'No longer needed',
        ])->assertRedirect();
        $request->refresh();
        $this->assertSame(ApprovalRequest::STATUS_CANCELLED, $request->status);
        $this->assertSame(ApprovalRequestStep::STATUS_APPROVED, $request->steps()->where('step_order', 1)->value('status'));
        $this->assertSame(ApprovalRequestStep::STATUS_CANCELLED, $request->steps()->where('step_order', 2)->value('status'));
    }

    private function submit(Employee $requester, User $user, array $approverIds): ApprovalRequest
    {
        return app(ApprovalRequestSubmissionService::class)->submit(
            new ApprovalDemoApprovable(random_int(1, PHP_INT_MAX), $requester),
            $approverIds,
            $user,
            (string) Str::uuid()
        );
    }

    private function workflow(int $min = 1, int $max = 5): ApprovalWorkflow
    {
        return ApprovalWorkflow::create([
            'code' => 'INBOX', 'version' => 1, 'name' => 'Inbox Workflow', 'module_key' => 'approval_demo',
            'min_approvers' => $min, 'max_approvers' => $max, 'hr_final_required' => false,
            'status' => ApprovalWorkflow::STATUS_ACTIVE,
        ]);
    }

    private function mappedEmployee(string $no, bool $eligible = false): array
    {
        $employee = $this->employee($no, $eligible);
        $user = $this->mapUser($employee);
        return [$employee->fresh(), $user];
    }

    private function mapUser(Employee $employee): User
    {
        $user = User::factory()->create();
        $employee->user_id = $user->id;
        $employee->save();
        return $user;
    }

    private function employee(string $no, bool $eligible = false): Employee
    {
        $company = Company::create(['company_code' => $no.'-C', 'company_name' => $no.' Company']);
        $base = Base::create(['base_code' => $no.'-B', 'base_name' => $no.' Base']);
        $unit = Unit::create(['base_id' => $base->id, 'unit_code' => $no.'-U', 'unit_name' => $no.' Unit']);
        $department = Department::create(['unit_id' => $unit->id, 'department_code' => $no.'-D', 'department_name' => $no.' Department']);
        $section = Section::create(['department_id' => $department->id, 'section_code' => $no.'-S', 'section_name' => $no.' Section']);
        $position = Position::create(['section_id' => $section->id, 'position_code' => $no.'-P', 'position_name' => $no.' Position']);
        $status = EmploymentStatus::create(['status_code' => $no.'-ES', 'status_name' => 'Regular']);
        $class = EmployeeClass::create(['class_code' => $no.'-EC', 'class_name' => 'Regular']);
        return Employee::create([
            'employee_no' => $no, 'last_name' => 'Test', 'first_name' => $no, 'gender' => 'Other', 'civil_status' => 'Single',
            'birth_date' => '1990-01-01', 'company_id' => $company->id, 'base_id' => $base->id, 'unit_id' => $unit->id,
            'department_id' => $department->id, 'section_id' => $section->id, 'position_id' => $position->id,
            'employment_status_id' => $status->id, 'employee_class_id' => $class->id, 'date_hired' => '2020-01-01',
            'is_active' => true, 'can_approve_requests' => $eligible,
        ]);
    }
}
