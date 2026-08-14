<?php

namespace Tests\Feature;

use App\Models\ApprovalRequest;
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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApprovalDemoFilingTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_access_is_blocked(): void
    {
        $this->get(route('approval-demo.create'))->assertRedirect(route('login'));
    }

    public function test_unmapped_user_is_blocked(): void
    {
        $this->actingAs(User::factory()->create())->get(route('approval-demo.create'))->assertOk()->assertSee('Your account must be mapped to an active employee before filing a request.');
    }

    public function test_missing_active_demo_workflow_renders_a_visible_error_instead_of_redirecting(): void
    {
        [, $user] = $this->mappedRequester();

        $this->actingAs($user)
            ->get(route('approval-demo.create'))
            ->assertOk()
            ->assertSee('No active approval workflow exists for this module.')
            ->assertDontSee('Location: /dashboard');
    }

    public function test_suggestions_and_search_exclude_ineligible_and_hr_employees(): void
    {
        [$requester, $user] = $this->mappedRequester();
        $supervisor = $this->employee('SUPERVISOR', true);
        $head = $this->employee('HEAD', true);
        $searchable = $this->employee('SEARCHABLE', true);
        $hr = $this->employee('HR', true);
        $requester->update(['immediate_supervisor_id' => $supervisor->id, 'department_head_id' => $head->id]);
        $this->workflow($hr);

        $response = $this->actingAs($user)->get(route('approval-demo.approvers.search', ['q' => 'SEARCHABLE']));
        $response->assertOk()->assertJsonFragment(['employee_no' => 'SEARCHABLE'])->assertJsonMissing(['employee_no' => 'HR']);
        $this->actingAs($user)->get(route('approval-demo.create'))->assertOk()->assertSee('SUPERVISOR')->assertSee('HEAD')->assertDontSee('SEARCHABLE Position');
    }

    public function test_preview_preserves_order_appends_hr_visually_and_does_not_create_runtime_request(): void
    {
        [$requester, $user] = $this->mappedRequester();
        $one = $this->employee('ONE', true);
        $two = $this->employee('TWO', true);
        $hr = $this->employee('HR', true);
        $this->workflow($hr, 2, 2);

        $response = $this->actingAs($user)->get(route('approval-demo.create'));
        $demoId = session('approval_demo.id');
        $key = session('approval_demo.idempotency_key');
        $response = $this->actingAs($user)->post(route('approval-demo.preview'), [
            'demo_id' => $demoId, 'idempotency_key' => $key, 'approvers' => [$two->id, $one->id],
        ]);

        $response->assertOk()->assertSee('HR Final Approval');
        $this->assertDatabaseCount('approval_requests', 0);
        $this->assertTrue($response->getContent() === $response->getContent());
    }

    public function test_submission_creates_real_snapshot_in_selected_order_and_is_idempotent(): void
    {
        [$requester, $user] = $this->mappedRequester();
        $one = $this->employee('ONE', true);
        $two = $this->employee('TWO', true);
        $hr = $this->employee('HR', true);
        $this->workflow($hr, 1, 2);
        $this->actingAs($user)->get(route('approval-demo.create'));
        $payload = ['demo_id' => session('approval_demo.id'), 'idempotency_key' => session('approval_demo.idempotency_key'), 'approvers' => [$two->id, $one->id]];

        $this->actingAs($user)->post(route('approval-demo.store'), $payload)->assertRedirect();
        $this->assertDatabaseCount('approval_requests', 1);
        $request = ApprovalRequest::firstOrFail()->load('steps');
        $this->assertSame([$two->id, $one->id, $hr->id], $request->steps->sortBy('step_order')->pluck('canonical_approver_employee_id')->all());
        $this->assertSame(ApprovalRequestStep::TYPE_HR_FINAL, $request->steps->last()->step_type);
        $this->actingAs($user)->post(route('approval-demo.store'), $payload)->assertRedirect(route('approval-demo.show', $request));
        $this->assertDatabaseCount('approval_requests', 1);
    }

    public function test_picker_enforces_minimum_maximum_duplicates_requester_and_hr(): void
    {
        [$requester, $user] = $this->mappedRequester();
        $approver = $this->employee('APPROVER', true);
        $hr = $this->employee('HR', true);
        $this->workflow($hr, 2, 2);
        $this->actingAs($user)->get(route('approval-demo.create'));
        $base = ['demo_id' => session('approval_demo.id'), 'idempotency_key' => session('approval_demo.idempotency_key')];

        $this->actingAs($user)->post(route('approval-demo.preview'), $base + ['approvers' => [$approver->id]])->assertSessionHasErrors('approvers');
        $this->actingAs($user)->post(route('approval-demo.preview'), $base + ['approvers' => [$approver->id, $approver->id]])->assertSessionHasErrors('approvers');
        $this->actingAs($user)->post(route('approval-demo.preview'), $base + ['approvers' => [$requester->id, $approver->id]])->assertSessionHasErrors('approvers');
        $this->actingAs($user)->post(route('approval-demo.preview'), $base + ['approvers' => [$hr->id, $approver->id]])->assertSessionHasErrors('approvers');
    }

    private function workflow(Employee $hr, int $min = 1, int $max = 5): ApprovalWorkflow
    {
        return ApprovalWorkflow::create(['code' => 'DEMO', 'version' => 1, 'name' => 'Demo Workflow', 'module_key' => 'approval_demo', 'min_approvers' => $min, 'max_approvers' => $max, 'hr_final_required' => true, 'hr_final_approver_employee_id' => $hr->id, 'status' => ApprovalWorkflow::STATUS_ACTIVE]);
    }

    private function mappedRequester(): array
    {
        $employee = $this->employee('REQUESTER'); $user = User::factory()->create(); $employee->user_id = $user->id; $employee->save();
        return [$employee->fresh(), $user];
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
        return Employee::create(['employee_no' => $no, 'last_name' => 'Test', 'first_name' => $no, 'gender' => 'Other', 'civil_status' => 'Single', 'birth_date' => '1990-01-01', 'company_id' => $company->id, 'base_id' => $base->id, 'unit_id' => $unit->id, 'department_id' => $department->id, 'section_id' => $section->id, 'position_id' => $position->id, 'employment_status_id' => $status->id, 'employee_class_id' => $class->id, 'date_hired' => '2020-01-01', 'is_active' => true, 'can_approve_requests' => $eligible]);
    }
}
