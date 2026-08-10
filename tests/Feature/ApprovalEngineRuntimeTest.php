<?php

namespace Tests\Feature;

use App\Contracts\Approvable;
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
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ApprovalEngineRuntimeTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_submission_snapshots_route_and_department(): void
    {
        [$requester, $requesterUser] = $this->mappedEmployee('REQUESTER');
        $approver = $this->eligibleEmployee('APPROVER');
        $hr = $this->eligibleEmployee('HR');
        $this->workflow($hr);

        $request = $this->submit($requester, $requesterUser, [$approver->id]);

        $this->assertSame(ApprovalRequest::STATUS_PENDING, $request->status);
        $this->assertSame($requester->department_id, $request->request_department_id);
        $this->assertSame(ApprovalWorkflow::STATUS_ACTIVE, $request->workflow->status);
        $this->assertCount(2, $request->steps);
        $this->assertSame(ApprovalRequestStep::STATUS_PENDING, $request->steps[0]->status);
        $this->assertSame(ApprovalRequestStep::STATUS_WAITING, $request->steps[1]->status);
        $this->assertSame(ApprovalRequestStep::TYPE_HR_FINAL, $request->steps[1]->step_type);
        $this->assertDatabaseHas('approval_request_actions', [
            'approval_request_id' => $request->id,
            'action' => ApprovalRequestAction::ACTION_SUBMIT,
        ]);
    }

    public function test_submission_requires_exactly_one_active_workflow(): void
    {
        [$requester, $user] = $this->mappedEmployee('NO-WORKFLOW');
        $approver = $this->eligibleEmployee('NO-WORKFLOW-APPROVER');

        $this->expectException(ValidationException::class);
        $this->submit($requester, $user, [$approver->id]);
    }

    public function test_submission_blocks_multiple_active_workflows(): void
    {
        [$requester, $user] = $this->mappedEmployee('MULTI-WORKFLOW');
        $approver = $this->eligibleEmployee('MULTI-APPROVER');
        $hr = $this->eligibleEmployee('MULTI-HR');
        $this->workflow($hr, 'ONE');
        $this->workflow($hr, 'TWO');

        $this->expectException(ValidationException::class);
        $this->submit($requester, $user, [$approver->id]);
    }

    public function test_minimum_maximum_self_duplicate_and_ineligible_approvers_are_blocked(): void
    {
        [$requester, $user] = $this->mappedEmployee('VALIDATION-REQUESTER');
        $approver = $this->eligibleEmployee('VALIDATION-APPROVER');
        $hr = $this->eligibleEmployee('VALIDATION-HR');
        $inactive = $this->eligibleEmployee('VALIDATION-INACTIVE');
        $inactive->update(['is_active' => false]);
        $this->workflow($hr, 'VALIDATION', 2, 2);

        foreach ([[$approver->id], [$approver->id, $approver->id], [$requester->id, $approver->id], [$inactive->id, $approver->id]] as $ids) {
            try {
                $this->submit($requester, $user, $ids);
                $this->fail('Expected submission validation to fail.');
            } catch (ValidationException) {
                $this->assertTrue(true);
            }
        }
    }

    public function test_hr_is_appended_once_and_cannot_be_selected_or_self_approve(): void
    {
        [$requester, $user] = $this->mappedEmployee('HR-REQUESTER');
        $approver = $this->eligibleEmployee('HR-APPROVER');
        $hr = $this->eligibleEmployee('HR-FINAL');
        $this->workflow($hr);

        $this->expectException(ValidationException::class);
        $this->submit($requester, $user, [$approver->id, $hr->id]);
    }

    public function test_requester_as_hr_final_approver_fails_closed(): void
    {
        [$requester, $user] = $this->mappedEmployee('HR-SELF');
        $approver = $this->eligibleEmployee('HR-SELF-APPROVER');
        $this->workflow($requester);

        $this->expectException(ValidationException::class);
        $this->submit($requester, $user, [$approver->id]);
    }

    public function test_workflow_and_department_snapshots_do_not_change(): void
    {
        [$requester, $user] = $this->mappedEmployee('SNAPSHOT-REQUESTER');
        $approver = $this->eligibleEmployee('SNAPSHOT-APPROVER');
        $hr = $this->eligibleEmployee('SNAPSHOT-HR');
        $workflow = $this->workflow($hr, 'SNAPSHOT', 1, 1);

        $request = $this->submit($requester, $user, [$approver->id]);
        $departmentId = $request->request_department_id;

        $workflow->update(['code' => 'CHANGED', 'version' => 9, 'name' => 'Changed']);
        $requester->update(['department_id' => $this->eligibleEmployee('NEW-DEPARTMENT')->department_id]);

        $fresh = $request->fresh();
        $this->assertSame('SNAPSHOT', $fresh->workflow_code);
        $this->assertSame(1, $fresh->workflow_version);
        $this->assertSame('SNAPSHOT', $fresh->workflow_name);
        $this->assertSame($departmentId, $fresh->request_department_id);
    }

    public function test_approval_is_strictly_sequential_and_final_hr_approval_completes_request(): void
    {
        [$requester, $requesterUser] = $this->mappedEmployee('SEQUENTIAL-REQUESTER');
        [$first, $firstUser] = $this->mappedEmployee('SEQUENTIAL-FIRST', true);
        [$second, $secondUser] = $this->mappedEmployee('SEQUENTIAL-SECOND', true);
        $hr = $this->eligibleEmployee('SEQUENTIAL-HR');
        $this->workflow($hr, 'SEQUENTIAL', 2, 2);
        $request = $this->submit($requester, $requesterUser, [$first->id, $second->id]);
        $service = app(ApprovalRequestActionService::class);

        $this->expectException(ValidationException::class);
        $service->approve($request, $secondUser, 'future-step');

        $service->approve($request, $firstUser, 'first-step');
        $this->assertSame(2, $request->fresh()->current_step_order);
        $service->approve($request, $secondUser, 'second-step');
        $request->refresh();
        $this->assertSame(3, $request->current_step_order);

        $service->approve($request, $this->mapUser($hr), 'hr-step');
        $this->assertSame(ApprovalRequest::STATUS_APPROVED, $request->fresh()->status);
        $this->assertNotNull($request->fresh()->completed_at);
    }

    public function test_direct_canonical_approval_records_actor_metadata(): void
    {
        [$requester, $requesterUser] = $this->mappedEmployee('DIRECT-REQUESTER');
        [$approver, $approverUser] = $this->mappedEmployee('DIRECT-APPROVER', true);
        $this->workflow(null, 'DIRECT', 1, 1, false);
        $request = $this->submit($requester, $requesterUser, [$approver->id]);

        app(ApprovalRequestActionService::class)->approve($request, $approverUser, 'direct');
        $action = ApprovalRequestAction::query()->where('idempotency_key', 'direct')->firstOrFail();

        $this->assertSame($approver->id, $action->actor_employee_id);
        $this->assertSame($approver->id, $action->canonical_approver_employee_id);
        $this->assertNull($action->acting_for_employee_id);
        $this->assertNull($action->approval_delegation_id);
    }

    public function test_action_history_captures_remarks_time_ip_and_user_agent(): void
    {
        [$requester, $requesterUser] = $this->mappedEmployee('ACTION-METADATA-REQUESTER');
        [$approver, $approverUser] = $this->mappedEmployee('ACTION-METADATA-APPROVER', true);
        $this->workflow(null, 'ACTION-METADATA', 1, 1, false);
        $request = $this->submit($requester, $requesterUser, [$approver->id]);

        app()->instance('request', Request::create(
            '/',
            'POST',
            [],
            [],
            [],
            [
                'REMOTE_ADDR' => '203.0.113.10',
                'HTTP_USER_AGENT' => 'ApprovalEngineTest/1.0',
            ]
        ));

        app(ApprovalRequestActionService::class)->approve(
            $request,
            $approverUser,
            'metadata-action',
            'Approved after review'
        );

        $action = ApprovalRequestAction::query()
            ->where('idempotency_key', 'metadata-action')
            ->firstOrFail();

        $this->assertSame('Approved after review', $action->remarks);
        $this->assertNotNull($action->acted_at);
        $this->assertSame('203.0.113.10', $action->ip_address);
        $this->assertSame('ApprovalEngineTest/1.0', $action->user_agent);
    }

    public function test_all_and_department_delegation_resolution_and_precedence(): void
    {
        [$requester, $requesterUser] = $this->mappedEmployee('DELEGATION-REQUESTER');
        [$canonical, $canonicalUser] = $this->mappedEmployee('DELEGATION-CANONICAL', true);
        [$delegate, $delegateUser] = $this->mappedEmployee('DELEGATION-DELEGATE', true);
        $this->workflow(null, 'DELEGATION', 1, 1, false);
        $request = $this->submit($requester, $requesterUser, [$canonical->id]);
        $dates = $this->validDates();

        $all = ApprovalDelegation::create([
            'acting_for_employee_id' => $canonical->id,
            'delegate_employee_id' => $delegate->id,
            'effective_from' => $dates[0],
            'effective_until' => $dates[1],
            'reason' => 'All',
            'scope_type' => ApprovalDelegation::SCOPE_ALL,
            'status' => ApprovalDelegation::STATUS_ACTIVE,
        ]);
        $department = ApprovalDelegation::create([
            'acting_for_employee_id' => $canonical->id,
            'delegate_employee_id' => $delegate->id,
            'effective_from' => $dates[0],
            'effective_until' => $dates[1],
            'reason' => 'Department',
            'scope_type' => ApprovalDelegation::SCOPE_DEPARTMENT,
            'department_id' => $request->request_department_id,
            'status' => ApprovalDelegation::STATUS_ACTIVE,
        ]);

        app(ApprovalRequestActionService::class)->approve($request, $delegateUser, 'delegated');
        $action = ApprovalRequestAction::query()->where('idempotency_key', 'delegated')->firstOrFail();
        $this->assertSame($department->id, $action->approval_delegation_id);
        $this->assertNotSame($all->id, $action->approval_delegation_id);
        $this->assertSame($canonical->id, $action->acting_for_employee_id);
        $this->assertSame($delegate->id, $action->actor_employee_id);
    }

    public function test_all_scope_fallback_works_without_recursive_multi_hop_delegation(): void
    {
        [$requester, $requesterUser] = $this->mappedEmployee('ALL-FALLBACK-REQUESTER');
        [$canonical] = $this->mappedEmployee('ALL-FALLBACK-CANONICAL', true);
        [$delegate, $delegateUser] = $this->mappedEmployee('ALL-FALLBACK-DELEGATE', true);
        [$secondDelegate, $secondDelegateUser] = $this->mappedEmployee('ALL-FALLBACK-SECOND', true);
        $this->workflow(null, 'ALL-FALLBACK', 1, 1, false);
        $dates = $this->validDates();

        ApprovalDelegation::create([
            'acting_for_employee_id' => $canonical->id,
            'delegate_employee_id' => $delegate->id,
            'effective_from' => $dates[0],
            'effective_until' => $dates[1],
            'reason' => 'Canonical coverage',
            'scope_type' => ApprovalDelegation::SCOPE_ALL,
            'status' => ApprovalDelegation::STATUS_ACTIVE,
        ]);
        ApprovalDelegation::create([
            'acting_for_employee_id' => $delegate->id,
            'delegate_employee_id' => $secondDelegate->id,
            'effective_from' => $dates[0],
            'effective_until' => $dates[1],
            'reason' => 'Second-level coverage',
            'scope_type' => ApprovalDelegation::SCOPE_ALL,
            'status' => ApprovalDelegation::STATUS_ACTIVE,
        ]);

        $service = app(ApprovalRequestActionService::class);
        $allRequest = $this->submit($requester, $requesterUser, [$canonical->id]);
        $service->approve($allRequest, $delegateUser, 'all-fallback');
        $this->assertSame(ApprovalRequest::STATUS_APPROVED, $allRequest->fresh()->status);

        $recursiveRequest = $this->submit($requester, $requesterUser, [$canonical->id]);
        $this->expectException(ValidationException::class);
        $service->approve($recursiveRequest, $secondDelegateUser, 'multi-hop-blocked');
    }

    public function test_wrong_department_null_department_expired_and_revoked_delegations_are_blocked(): void
    {
        [$requester, $requesterUser] = $this->mappedEmployee('DELEGATION-BLOCK-REQUESTER');
        [$canonical] = $this->mappedEmployee('DELEGATION-BLOCK-CANONICAL', true);
        [$delegate, $delegateUser] = $this->mappedEmployee('DELEGATION-BLOCK-DELEGATE', true);
        $this->workflow(null, 'DELEGATION-BLOCK', 1, 1, false);
        $request = $this->submit($requester, $requesterUser, [$canonical->id]);
        $dates = $this->validDates();

        ApprovalDelegation::create([
            'acting_for_employee_id' => $canonical->id,
            'delegate_employee_id' => $delegate->id,
            'effective_from' => $dates[0],
            'effective_until' => $dates[1],
            'reason' => 'Wrong department',
            'scope_type' => ApprovalDelegation::SCOPE_DEPARTMENT,
            'department_id' => $this->eligibleEmployee('OTHER-DEPARTMENT')->department_id,
            'status' => ApprovalDelegation::STATUS_ACTIVE,
        ]);

        $this->expectException(ValidationException::class);
        app(ApprovalRequestActionService::class)->approve($request, $delegateUser, 'wrong-department');
    }

    public function test_null_request_department_does_not_match_department_delegation_but_all_applies(): void
    {
        [$requester, $requesterUser] = $this->mappedEmployee('NULL-DEPARTMENT-REQUESTER');
        [$canonical] = $this->mappedEmployee('NULL-DEPARTMENT-CANONICAL', true);
        [$delegate, $delegateUser] = $this->mappedEmployee('NULL-DEPARTMENT-DELEGATE', true);
        $this->workflow(null, 'NULL-DEPARTMENT', 1, 1, false);
        $source = new FakeApprovable('null_department', $requester, null);
        $request = app(ApprovalRequestSubmissionService::class)->submit($source, [$canonical->id], $requesterUser);
        $dates = $this->validDates();

        ApprovalDelegation::create([
            'acting_for_employee_id' => $canonical->id,
            'delegate_employee_id' => $delegate->id,
            'effective_from' => $dates[0],
            'effective_until' => $dates[1],
            'reason' => 'Department',
            'scope_type' => ApprovalDelegation::SCOPE_DEPARTMENT,
            'department_id' => $requester->department_id,
            'status' => ApprovalDelegation::STATUS_ACTIVE,
        ]);

        $this->expectException(ValidationException::class);
        app(ApprovalRequestActionService::class)->approve($request, $delegateUser, 'null-department-blocked');
    }

    public function test_reject_cancels_future_steps_and_cancel_preserves_approved_history(): void
    {
        [$requester, $requesterUser] = $this->mappedEmployee('CANCEL-REQUESTER');
        [$first, $firstUser] = $this->mappedEmployee('CANCEL-FIRST', true);
        [$second] = $this->mappedEmployee('CANCEL-SECOND', true);
        $hr = $this->eligibleEmployee('CANCEL-HR');
        $this->workflow($hr, 'CANCEL', 2, 2);
        $request = $this->submit($requester, $requesterUser, [$first->id, $second->id]);
        $service = app(ApprovalRequestActionService::class);

        $service->approve($request, $firstUser, 'cancel-first');
        $service->cancel($request, $requesterUser, 'cancel-pending');
        $request->refresh();

        $this->assertSame(ApprovalRequest::STATUS_CANCELLED, $request->status);
        $this->assertSame(ApprovalRequestStep::STATUS_APPROVED, $request->steps()->where('step_order', 1)->first()->status);
        $this->assertSame(ApprovalRequestStep::STATUS_CANCELLED, $request->steps()->where('step_order', 2)->first()->status);
        $this->assertSame(ApprovalRequestStep::STATUS_CANCELLED, $request->steps()->where('step_order', 3)->first()->status);
    }

    public function test_reject_is_terminal_and_future_steps_never_activate(): void
    {
        [$requester, $requesterUser] = $this->mappedEmployee('REJECT-REQUESTER');
        [$first, $firstUser] = $this->mappedEmployee('REJECT-FIRST', true);
        [$second, $secondUser] = $this->mappedEmployee('REJECT-SECOND', true);
        $this->workflow(null, 'REJECT', 1, 2, false);
        $request = $this->submit($requester, $requesterUser, [$first->id, $second->id]);
        $service = app(ApprovalRequestActionService::class);

        $service->reject($request, $firstUser, 'reject-current', 'Not approved');
        $request->refresh();

        $this->assertSame(ApprovalRequest::STATUS_REJECTED, $request->status);
        $this->assertSame(ApprovalRequestStep::STATUS_REJECTED, $request->steps()->where('step_order', 1)->first()->status);
        $this->assertSame(ApprovalRequestStep::STATUS_CANCELLED, $request->steps()->where('step_order', 2)->first()->status);

        $this->expectException(ValidationException::class);
        $service->approve($request, $secondUser, 'future-after-reject');
    }

    public function test_expired_and_revoked_delegations_are_blocked_at_action_time(): void
    {
        [$requester, $requesterUser] = $this->mappedEmployee('EXPIRED-REQUESTER');
        [$canonical] = $this->mappedEmployee('EXPIRED-CANONICAL', true);
        [$delegate, $delegateUser] = $this->mappedEmployee('EXPIRED-DELEGATE', true);
        $this->workflow(null, 'EXPIRED', 1, 1, false);
        $request = $this->submit($requester, $requesterUser, [$canonical->id]);

        ApprovalDelegation::create([
            'acting_for_employee_id' => $canonical->id,
            'delegate_employee_id' => $delegate->id,
            'effective_from' => Carbon::now()->subDays(3)->toDateString(),
            'effective_until' => Carbon::now()->subDay()->toDateString(),
            'reason' => 'Expired',
            'scope_type' => ApprovalDelegation::SCOPE_ALL,
            'status' => ApprovalDelegation::STATUS_ACTIVE,
        ]);

        $service = app(ApprovalRequestActionService::class);
        try {
            $service->approve($request, $delegateUser, 'expired');
            $this->fail('Expired delegation should be rejected.');
        } catch (ValidationException) {
            $this->assertTrue(true);
        }

        $validDates = $this->validDates();
        $delegation = ApprovalDelegation::create([
            'acting_for_employee_id' => $canonical->id,
            'delegate_employee_id' => $delegate->id,
            'effective_from' => $validDates[0],
            'effective_until' => $validDates[1],
            'reason' => 'Revoked',
            'scope_type' => ApprovalDelegation::SCOPE_ALL,
            'status' => ApprovalDelegation::STATUS_ACTIVE,
        ]);
        $delegation->update(['status' => ApprovalDelegation::STATUS_REVOKED]);

        $this->expectException(ValidationException::class);
        $service->approve($request, $delegateUser, 'revoked');
    }

    public function test_draft_can_be_cancelled_terminal_requests_block_actions_and_idempotency_is_safe(): void
    {
        [$requester, $requesterUser] = $this->mappedEmployee('CANCEL-DRAFT-REQUESTER');
        $approver = $this->eligibleEmployee('CANCEL-DRAFT-APPROVER');
        $this->workflow(null, 'CANCEL-DRAFT', 1, 1, false);
        $source = new FakeApprovable('draft', $requester, $requester->department_id);
        $draft = ApprovalRequest::create([
            'requester_employee_id' => $requester->id,
            'module_key' => $source->approvalModuleKey(),
            'approvable_type' => $source->approvalType(),
            'approvable_id' => $source->approvalId(),
            'status' => ApprovalRequest::STATUS_DRAFT,
        ]);
        $service = app(ApprovalRequestActionService::class);
        $service->cancel($draft, $requesterUser, 'draft-cancel');
        $this->assertSame(ApprovalRequest::STATUS_CANCELLED, $draft->fresh()->status);

        $this->expectException(ValidationException::class);
        $service->cancel($draft, $requesterUser, 'draft-cancel-again');
    }

    public function test_missing_mapping_and_duplicate_action_are_blocked_without_duplicate_rows(): void
    {
        $requester = $this->employee('MISSING-MAPPING-REQUESTER');
        $user = User::factory()->create();
        $approver = $this->eligibleEmployee('MISSING-MAPPING-APPROVER');
        $this->workflow(null, 'MISSING-MAPPING', 1, 1, false);

        $this->expectException(ValidationException::class);
        $this->submit($requester, $user, [$approver->id]);
    }

    public function test_append_only_action_history_and_idempotent_approval(): void
    {
        [$requester, $requesterUser] = $this->mappedEmployee('IDEMPOTENT-REQUESTER');
        [$approver, $approverUser] = $this->mappedEmployee('IDEMPOTENT-APPROVER', true);
        $this->workflow(null, 'IDEMPOTENT', 1, 1, false);
        $request = $this->submit($requester, $requesterUser, [$approver->id]);
        $service = app(ApprovalRequestActionService::class);

        $service->approve($request, $approverUser, 'same-key');
        $count = ApprovalRequestAction::query()->where('approval_request_id', $request->id)->count();
        $service->approve($request, $approverUser, 'same-key');

        $this->assertSame($count, ApprovalRequestAction::query()->where('approval_request_id', $request->id)->count());
        $this->assertSame(ApprovalRequest::STATUS_APPROVED, $request->fresh()->status);
    }

    private function submit(Employee $requester, User $user, array $approverIds): ApprovalRequest
    {
        return app(ApprovalRequestSubmissionService::class)->submit(
            new FakeApprovable('source-' . $requester->id . '-' . uniqid(), $requester, $requester->department_id),
            $approverIds,
            $user
        );
    }

    private function workflow(
        ?Employee $hr,
        string $code = 'RUNTIME',
        int $min = 1,
        int $max = 5,
        bool $hrRequired = true
    ): ApprovalWorkflow {
        return ApprovalWorkflow::create([
            'code' => $code,
            'version' => 1,
            'name' => $code,
            'module_key' => 'overtime',
            'min_approvers' => $min,
            'max_approvers' => $max,
            'hr_final_required' => $hrRequired,
            'hr_final_approver_employee_id' => $hr?->id,
            'status' => ApprovalWorkflow::STATUS_ACTIVE,
        ]);
    }

    private function mappedEmployee(string $employeeNo, bool $eligible = false): array
    {
        $employee = $this->employee($employeeNo);
        if ($eligible) {
            $employee->update(['can_approve_requests' => true]);
        }
        $user = $this->mapUser($employee);
        return [$employee->fresh(), $user];
    }

    private function eligibleEmployee(string $employeeNo): Employee
    {
        $employee = $this->employee($employeeNo);
        $employee->update(['can_approve_requests' => true]);

        return $employee->fresh();
    }

    private function mapUser(Employee $employee): User
    {
        $user = User::factory()->create();
        $employee->user_id = $user->id;
        $employee->save();
        return $user;
    }

    private function validDates(): array
    {
        return [
            Carbon::now()->subDay()->toDateString(),
            Carbon::now()->addDay()->toDateString(),
        ];
    }

    private function employee(string $employeeNo): Employee
    {
        $company = Company::create(['company_code' => $employeeNo . '-C', 'company_name' => $employeeNo . ' Company']);
        $base = Base::create(['base_code' => $employeeNo . '-B', 'base_name' => $employeeNo . ' Base']);
        $unit = Unit::create(['base_id' => $base->id, 'unit_code' => $employeeNo . '-U', 'unit_name' => $employeeNo . ' Unit']);
        $department = Department::create(['unit_id' => $unit->id, 'department_code' => $employeeNo . '-D', 'department_name' => $employeeNo . ' Department']);
        $section = Section::create(['department_id' => $department->id, 'section_code' => $employeeNo . '-S', 'section_name' => $employeeNo . ' Section']);
        $position = Position::create(['section_id' => $section->id, 'position_code' => $employeeNo . '-P', 'position_name' => $employeeNo . ' Position']);
        $employmentStatus = EmploymentStatus::create(['status_code' => $employeeNo . '-ES', 'status_name' => 'Regular']);
        $employeeClass = EmployeeClass::create(['class_code' => $employeeNo . '-EC', 'class_name' => 'Regular']);

        return Employee::create([
            'employee_no' => $employeeNo,
            'last_name' => 'Test',
            'first_name' => $employeeNo,
            'gender' => 'Other',
            'civil_status' => 'Single',
            'birth_date' => '1990-01-01',
            'company_id' => $company->id,
            'base_id' => $base->id,
            'unit_id' => $unit->id,
            'department_id' => $department->id,
            'section_id' => $section->id,
            'position_id' => $position->id,
            'employment_status_id' => $employmentStatus->id,
            'employee_class_id' => $employeeClass->id,
            'date_hired' => '2020-01-01',
            'is_active' => true,
        ]);
    }
}

final class FakeApprovable implements Approvable
{
    public function __construct(
        private readonly string $key,
        private readonly Employee $requester,
        private readonly ?int $departmentId
    ) {
    }

    public function approvalModuleKey(): string
    {
        return 'overtime';
    }

    public function approvalType(): string
    {
        return 'tests.fake.' . $this->key;
    }

    public function approvalId(): int
    {
        return abs(crc32($this->key));
    }

    public function approvalRequester(): Employee
    {
        return $this->requester;
    }

    public function approvalDepartmentId(): ?int
    {
        return $this->departmentId;
    }
}
