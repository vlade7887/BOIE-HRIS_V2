<?php

namespace Tests\Feature;

use App\Models\ApprovalDelegation;
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
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ApprovalWorkflowFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_approver_capability_defaults_to_false_and_can_be_enabled_through_employee_admin_update(): void
    {
        $user = User::factory()->create();
        $employee = $this->employee('CAPABILITY-001');

        $this->assertFalse($employee->can_approve_requests);

        $response = $this->actingAs($user)->put(
            route('employees.update', $employee),
            $this->employeePayload($employee, ['can_approve_requests' => 1])
        );

        $response->assertRedirect(route('employees.show', $employee));
        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'can_approve_requests' => 1,
        ]);
    }

    public function test_workflow_template_stores_module_limits_and_hr_settings(): void
    {
        $user = User::factory()->create();
        $approver = $this->employee('TEMPLATE-HR');
        $approver->update(['can_approve_requests' => true]);

        $response = $this->actingAs($user)->post(route('approval-workflows.store'), [
            'code' => 'LEAVE-TEMPLATE',
            'version' => 1,
            'name' => 'Leave Template',
            'description' => 'Reusable template',
            'module_key' => 'leave',
            'min_approvers' => 1,
            'max_approvers' => 5,
            'hr_final_required' => 1,
            'hr_final_approver_employee_id' => $approver->id,
            'status' => 'draft',
        ]);

        $workflow = ApprovalWorkflow::where('code', 'LEAVE-TEMPLATE')->firstOrFail();
        $response->assertRedirect(route('approval-workflows.show', $workflow));
        $this->assertDatabaseHas('approval_workflows', [
            'id' => $workflow->id,
            'module_key' => 'leave',
            'min_approvers' => 1,
            'max_approvers' => 5,
            'hr_final_required' => 1,
            'hr_final_approver_employee_id' => $approver->id,
        ]);
    }

    public function test_invalid_module_and_minimum_greater_than_maximum_are_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('approval-workflows.store'), [
            'code' => 'INVALID-MODULE',
            'version' => 1,
            'name' => 'Invalid Module',
            'module_key' => 'travel',
            'min_approvers' => 1,
            'max_approvers' => 5,
            'hr_final_required' => 0,
            'status' => 'draft',
        ])->assertSessionHasErrors('module_key');

        $this->actingAs($user)->post(route('approval-workflows.store'), [
            'code' => 'INVALID-LIMITS',
            'version' => 1,
            'name' => 'Invalid Limits',
            'module_key' => 'leave',
            'min_approvers' => 4,
            'max_approvers' => 2,
            'hr_final_required' => 0,
            'status' => 'draft',
        ])->assertSessionHasErrors('max_approvers');
    }

    public function test_active_template_requires_a_valid_eligible_hr_final_approver(): void
    {
        $user = User::factory()->create();
        $approver = $this->employee('ACTIVE-HR');

        $this->actingAs($user)->post(route('approval-workflows.store'), [
            'code' => 'ACTIVE-TEMPLATE',
            'version' => 1,
            'name' => 'Active Template',
            'module_key' => 'leave',
            'min_approvers' => 1,
            'max_approvers' => 5,
            'hr_final_required' => 1,
            'status' => 'active',
        ])->assertSessionHasErrors('hr_final_approver_employee_id');

        $approver->update(['can_approve_requests' => true]);

        $response = $this->actingAs($user)->post(route('approval-workflows.store'), [
            'code' => 'ACTIVE-TEMPLATE',
            'version' => 1,
            'name' => 'Active Template',
            'module_key' => 'leave',
            'min_approvers' => 1,
            'max_approvers' => 5,
            'hr_final_required' => 1,
            'hr_final_approver_employee_id' => $approver->id,
            'status' => 'active',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('approval_workflows', ['code' => 'ACTIVE-TEMPLATE', 'status' => 'active']);
    }

    public function test_code_can_have_multiple_versions_but_duplicate_code_and_version_is_rejected(): void
    {
        $user = User::factory()->create();

        foreach ([1, 2] as $version) {
            $this->actingAs($user)->post(route('approval-workflows.store'), [
                'code' => 'VERSIONED',
                'version' => $version,
                'name' => 'Version ' . $version,
                'module_key' => 'leave',
                'min_approvers' => 1,
                'max_approvers' => 5,
                'hr_final_required' => 0,
                'status' => 'draft',
            ])->assertRedirect();
        }

        $this->actingAs($user)->post(route('approval-workflows.store'), [
            'code' => 'VERSIONED',
            'version' => 1,
            'name' => 'Duplicate',
            'module_key' => 'leave',
            'min_approvers' => 1,
            'max_approvers' => 5,
            'hr_final_required' => 0,
            'status' => 'draft',
        ])->assertSessionHasErrors('code');
    }

    public function test_direct_edit_access_to_active_template_is_blocked(): void
    {
        $user = User::factory()->create();
        $workflow = ApprovalWorkflow::create([
            'code' => 'LOCKED',
            'version' => 1,
            'name' => 'Locked',
            'module_key' => 'leave',
            'min_approvers' => 1,
            'max_approvers' => 5,
            'hr_final_required' => false,
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->get(route('approval-workflows.edit', $workflow))
            ->assertRedirect(route('approval-workflows.index'))
            ->assertSessionHasErrors('workflow');
    }

    public function test_legacy_workflow_tables_are_renamed_and_routes_are_absent(): void
    {
        $this->assertTrue(Schema::hasTable('legacy_workflow_assignments'));
        $this->assertTrue(Schema::hasTable('legacy_workflow_steps'));
        $this->assertFalse(Schema::hasTable('workflow_assignments'));
        $this->assertFalse(Schema::hasTable('workflow_steps'));

        foreach ([
            'workflow-assignments.index',
            'workflow-assignments.store',
            'workflow-assignments.archive',
            'workflow-steps.index',
            'workflow-steps.store',
            'workflow-steps.archive',
        ] as $routeName) {
            $this->assertFalse(Route::has($routeName), "Retired route [{$routeName}] should not exist.");
        }

        $this->assertSame(0, (int) \DB::table('legacy_workflow_assignments')->count());
        $this->assertSame(0, (int) \DB::table('legacy_workflow_steps')->count());
    }

    public function test_scoped_delegation_validation_and_conflicts_are_enforced(): void
    {
        $user = User::factory()->create();
        $actingFor = $this->employee('DELEGATE-ACTING');
        $delegate = $this->employee('DELEGATE-ONE');
        $delegate->update(['can_approve_requests' => true]);
        $sameDepartment = $this->employee('DELEGATE-SAME');
        $sameDepartment->update(['department_id' => $actingFor->department_id]);
        $sameDepartment->update(['can_approve_requests' => true]);
        $differentDepartment = $this->employee('DELEGATE-DIFFERENT');
        $differentDepartment->update(['can_approve_requests' => true]);
        $allDelegate = $this->employee('DELEGATE-ALL');
        $allDelegate->update(['can_approve_requests' => true]);

        $base = [
            'acting_for_employee_id' => $actingFor->id,
            'delegate_employee_id' => $delegate->id,
            'effective_from' => '2026-09-01',
            'effective_until' => '2026-09-30',
            'reason' => 'Coverage',
            'status' => 'expired',
        ];

        $this->actingAs($user)->post(route('approval-delegations.store'), array_merge($base, [
            'scope_type' => 'department',
        ]))->assertSessionHasErrors('department_id');

        $this->actingAs($user)->post(route('approval-delegations.store'), array_merge($base, [
            'scope_type' => 'department',
            'department_id' => $actingFor->department_id,
        ]))->assertRedirect();

        $this->actingAs($user)->post(route('approval-delegations.store'), array_merge($base, [
            'delegate_employee_id' => $sameDepartment->id,
            'scope_type' => 'department',
            'department_id' => $actingFor->department_id,
        ]))->assertSessionHasErrors('effective_from');

        $this->actingAs($user)->post(route('approval-delegations.store'), array_merge($base, [
            'delegate_employee_id' => $differentDepartment->id,
            'scope_type' => 'department',
            'department_id' => $differentDepartment->department_id,
        ]))->assertRedirect();

        $this->actingAs($user)->post(route('approval-delegations.store'), array_merge($base, [
            'delegate_employee_id' => $allDelegate->id,
            'scope_type' => 'all',
        ]))->assertSessionHasErrors('effective_from');
    }

    public function test_self_delegation_and_direct_reverse_loop_are_rejected(): void
    {
        $user = User::factory()->create();
        $a = $this->employee('LOOP-A');
        $b = $this->employee('LOOP-B');
        $a->update(['can_approve_requests' => true]);
        $b->update(['can_approve_requests' => true]);

        $payload = [
            'acting_for_employee_id' => $a->id,
            'delegate_employee_id' => $a->id,
            'effective_from' => '2026-10-01',
            'effective_until' => '2026-10-31',
            'reason' => 'Invalid',
            'scope_type' => 'all',
            'status' => 'revoked',
        ];

        $this->actingAs($user)->post(route('approval-delegations.store'), $payload)
            ->assertSessionHasErrors('delegate_employee_id');

        $this->actingAs($user)->post(route('approval-delegations.store'), array_merge($payload, [
            'delegate_employee_id' => $b->id,
        ]))->assertRedirect();

        $this->actingAs($user)->post(route('approval-delegations.store'), array_merge($payload, [
            'acting_for_employee_id' => $b->id,
            'delegate_employee_id' => $a->id,
        ]))->assertSessionHasErrors('delegate_employee_id');
    }

    public function test_delegation_revoke_retains_metadata_and_status(): void
    {
        $user = User::factory()->create();
        $actingFor = $this->employee('REVOKE-ACTING');
        $delegate = $this->employee('REVOKE-DELEGATE');
        $delegate->update(['can_approve_requests' => true]);

        $delegation = ApprovalDelegation::create([
            'acting_for_employee_id' => $actingFor->id,
            'delegate_employee_id' => $delegate->id,
            'effective_from' => '2026-11-01',
            'effective_until' => '2026-11-30',
            'reason' => 'Vacation',
            'scope_type' => 'all',
            'status' => 'active',
        ]);

        $this->actingAs($user)->post(route('approval-delegations.revoke', $delegation))->assertRedirect();

        $this->assertDatabaseHas('approval_delegations', [
            'id' => $delegation->id,
            'status' => 'revoked',
            'revoked_by_user_id' => $user->id,
        ]);
        $this->assertNotNull($delegation->fresh()->revoked_at);
    }

    public function test_mapping_and_audit_capture_both_actor_fields(): void
    {
        $user = User::factory()->create();
        $employee = $this->employee('MAPPED-ACTOR');
        $employee->user_id = $user->id;
        $employee->save();

        $this->actingAs($user)->post(route('approval-workflows.store'), [
            'code' => 'AUDIT-TEMPLATE',
            'version' => 1,
            'name' => 'Audit Template',
            'module_key' => 'overtime',
            'min_approvers' => 1,
            'max_approvers' => 5,
            'hr_final_required' => false,
            'status' => 'draft',
        ])->assertRedirect();

        $this->assertDatabaseHas('approval_audit_logs', [
            'event_type' => 'approval_workflow.created',
            'actor_user_id' => $user->id,
            'actor_employee_id' => $employee->id,
        ]);
    }

    public function test_unmapped_employee_mapping_edit_lists_only_available_users(): void
    {
        $authUser = User::factory()->create();
        $employee = $this->employee('MAPPING-UNMAPPED');
        $otherEmployee = $this->employee('MAPPING-OTHER');
        $mappedUser = User::factory()->create();
        $availableUser = User::factory()->create();

        $otherEmployee->user_id = $mappedUser->id;
        $otherEmployee->save();

        $response = $this->actingAs($authUser)
            ->get(route('employee-user-mappings.edit', $employee));

        $response->assertOk()
            ->assertSee('value="' . $availableUser->id . '"', false)
            ->assertDontSee('value="' . $mappedUser->id . '"', false);
    }

    public function test_mapped_employee_can_open_edit_and_keep_current_user_available(): void
    {
        $authUser = User::factory()->create();
        $employee = $this->employee('MAPPING-CURRENT');
        $otherEmployee = $this->employee('MAPPING-EXCLUDED');
        $currentUser = User::factory()->create();
        $otherMappedUser = User::factory()->create();

        $employee->user_id = $currentUser->id;
        $employee->save();
        $otherEmployee->user_id = $otherMappedUser->id;
        $otherEmployee->save();

        $response = $this->actingAs($authUser)
            ->get(route('employee-user-mappings.edit', $employee));

        $response->assertOk()
            ->assertSee('value="' . $currentUser->id . '" selected', false)
            ->assertDontSee('value="' . $otherMappedUser->id . '"', false);
    }

    public function test_employee_user_mapping_can_still_be_unmapped(): void
    {
        $authUser = User::factory()->create();
        $employee = $this->employee('MAPPING-REMOVE');
        $mappedUser = User::factory()->create();

        $employee->user_id = $mappedUser->id;
        $employee->save();

        $this->actingAs($authUser)
            ->post(route('employee-user-mappings.unmap', $employee))
            ->assertRedirect(route('employee-user-mappings.index'));

        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'user_id' => null,
        ]);
    }

    public function test_current_archive_revoke_and_unmap_routes_exist(): void
    {
        foreach ([
            'employee-user-mappings.unmap',
            'approval-workflows.archive',
            'approval-delegations.revoke',
            'approval-delegations.archive',
        ] as $routeName) {
            $this->assertTrue(Route::has($routeName), "Current route [{$routeName}] should exist.");
        }
    }

    public function test_delegation_scope_form_reacts_and_preserves_selected_department(): void
    {
        $user = User::factory()->create();
        $actingFor = $this->employee('SCOPE-UI-ACTING');
        $delegate = $this->employee('SCOPE-UI-DELEGATE');
        $delegate->update(['can_approve_requests' => true]);

        $createResponse = $this->actingAs($user)->get(route('approval-delegations.create'));

        $createResponse
            ->assertOk()
            ->assertSee('id="department-field-wrapper"', false)
            ->assertSee('departmentFieldWrapper.hidden = isAllApprovals', false)
            ->assertSee('departmentField.disabled = isAllApprovals', false)
            ->assertSee("departmentField.value = '';", false)
            ->assertSee("scopeField.addEventListener('change', toggleDepartmentField)", false);

        $delegation = ApprovalDelegation::create([
            'acting_for_employee_id' => $actingFor->id,
            'delegate_employee_id' => $delegate->id,
            'effective_from' => '2026-12-01',
            'effective_until' => '2026-12-31',
            'reason' => 'Department coverage',
            'scope_type' => ApprovalDelegation::SCOPE_DEPARTMENT,
            'department_id' => $actingFor->department_id,
            'status' => ApprovalDelegation::STATUS_ACTIVE,
        ]);

        $this->actingAs($user)
            ->get(route('approval-delegations.edit', $delegation))
            ->assertOk()
            ->assertSee('value="' . $actingFor->department_id . '" selected', false);
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

    private function employeePayload(Employee $employee, array $overrides = []): array
    {
        return array_merge([
            'employee_no' => $employee->employee_no,
            'last_name' => $employee->last_name,
            'first_name' => $employee->first_name,
            'gender' => $employee->gender,
            'civil_status' => $employee->civil_status,
            'birth_date' => $employee->birth_date->format('Y-m-d'),
            'company_id' => $employee->company_id,
            'base_id' => $employee->base_id,
            'unit_id' => $employee->unit_id,
            'department_id' => $employee->department_id,
            'section_id' => $employee->section_id,
            'position_id' => $employee->position_id,
            'employment_status_id' => $employee->employment_status_id,
            'employee_class_id' => $employee->employee_class_id,
            'date_hired' => $employee->date_hired->format('Y-m-d'),
            'nationality' => 'Filipino',
            'is_active' => 1,
            'can_approve_requests' => 0,
        ], $overrides);
    }
}
