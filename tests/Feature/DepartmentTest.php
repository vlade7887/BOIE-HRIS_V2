<?php

namespace Tests\Feature;

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

class DepartmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_the_department_index(): void
    {
        $user = User::factory()->create();
        $department = Department::create(['department_code' => 'DEP-001', 'department_name' => 'Human Resources']);

        $response = $this->actingAs($user)->get(route('departments.index'));

        $response->assertOk()->assertSee($department->department_name);
    }

    public function test_guest_is_redirected_from_the_department_index(): void
    {
        $this->get(route('departments.index'))->assertRedirect(route('login'));
    }

    public function test_department_can_be_created_without_a_unit(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('departments.store'), [
            'department_code' => 'DEP-001',
            'department_name' => 'Human Resources',
            'remarks' => 'Independent department',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('departments.index'))
            ->assertSessionHas('success', 'Department created successfully.');
        $this->assertDatabaseHas('departments', ['department_code' => 'DEP-001', 'unit_id' => null]);
    }

    public function test_department_can_be_updated(): void
    {
        $user = User::factory()->create();
        $department = Department::create(['department_code' => 'DEP-002', 'department_name' => 'Old Name']);

        $response = $this->actingAs($user)->put(route('departments.update', $department), [
            'department_code' => 'DEP-003',
            'department_name' => 'New Name',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('departments.index'))
            ->assertSessionHas('success', 'Department updated successfully.');
        $this->assertDatabaseHas('departments', ['id' => $department->id, 'department_code' => 'DEP-003', 'department_name' => 'New Name']);
    }

    public function test_department_show_page_displays_details(): void
    {
        $user = User::factory()->create();
        $department = Department::create(['department_code' => 'DEP-004', 'department_name' => 'Operations']);

        $this->actingAs($user)->get(route('departments.show', $department))
            ->assertOk()->assertSee('DEP-004')->assertSee('Operations')->assertSee('Not assigned');
    }

    public function test_department_index_can_search_by_code_or_name(): void
    {
        $user = User::factory()->create();
        Department::create(['department_code' => 'ALPHA-01', 'department_name' => 'Alpha Department']);
        Department::create(['department_code' => 'BETA-01', 'department_name' => 'Beta Department']);

        $this->actingAs($user)->get(route('departments.index', ['search' => 'alpha']))
            ->assertOk()->assertSee('Alpha Department')->assertDontSee('Beta Department');
    }

    public function test_department_index_paginates_results(): void
    {
        $user = User::factory()->create();
        foreach (range(1, 12) as $index) {
            Department::create(['department_code' => 'DEP-' . $index, 'department_name' => 'Department ' . $index]);
        }

        $this->actingAs($user)->get(route('departments.index'))->assertViewHas('departments', function ($departments) {
            return $departments->count() === 10 && $departments->hasPages();
        });
    }

    public function test_department_can_be_archived_and_restored_with_flash_messages(): void
    {
        $user = User::factory()->create();
        $department = Department::create(['department_code' => 'DEP-005', 'department_name' => 'Archive Me']);

        $this->actingAs($user)->post(route('departments.archive', $department))
            ->assertRedirect(route('departments.index'))
            ->assertSessionHas('success', 'Department archived successfully.');
        $this->assertSoftDeleted('departments', ['id' => $department->id]);

        $this->actingAs($user)->get(route('departments.index', ['view' => 'archived']))
            ->assertOk()->assertSee('Archive Me')->assertSee('Restore');

        $this->actingAs($user)->post(route('departments.restore', $department->id))
            ->assertRedirect(route('departments.index'))
            ->assertSessionHas('success', 'Department restored successfully.');
        $this->assertDatabaseHas('departments', ['id' => $department->id, 'deleted_at' => null]);
    }

    public function test_department_archive_is_blocked_when_active_employee_references_it(): void
    {
        $user = User::factory()->create();
        $department = $this->createDepartmentWithActiveEmployee();

        $this->actingAs($user)->post(route('departments.archive', $department))
            ->assertSessionHasErrors(['department']);
        $this->assertDatabaseHas('departments', ['id' => $department->id, 'deleted_at' => null]);
    }

    public function test_department_validation_returns_summary_and_preserves_old_input(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->from(route('departments.create'))
            ->post(route('departments.store'), ['department_code' => 'BAD', 'department_name' => ''])
            ->assertRedirect(route('departments.create'))
            ->assertSessionHasErrors(['department_name']);
    }

    private function createDepartmentWithActiveEmployee(): Department
    {
        $company = Company::create(['company_code' => 'CMP-D', 'company_name' => 'Department Company']);
        $base = Base::create(['base_code' => 'BASE-D', 'base_name' => 'Department Base']);
        $unit = Unit::create(['base_id' => $base->id, 'unit_code' => 'UNIT-D', 'unit_name' => 'Department Unit']);
        $department = Department::create(['unit_id' => $unit->id, 'department_code' => 'DEP-P', 'department_name' => 'Protected Department']);
        $section = Section::create(['department_id' => $department->id, 'section_code' => 'SEC-D', 'section_name' => 'Department Section']);
        $position = Position::create(['section_id' => $section->id, 'position_code' => 'POS-D', 'position_name' => 'Department Position']);
        $employmentStatus = EmploymentStatus::create(['status_code' => 'STAT-D', 'status_name' => 'Regular']);
        $employeeClass = EmployeeClass::create(['class_code' => 'CLASS-D', 'class_name' => 'Regular']);

        Employee::create([
            'employee_no' => 'EMP-D', 'last_name' => 'Doe', 'first_name' => 'Jane', 'gender' => 'Female',
            'civil_status' => 'Single', 'birth_date' => '1990-01-01', 'company_id' => $company->id,
            'base_id' => $base->id, 'unit_id' => $unit->id, 'department_id' => $department->id,
            'section_id' => $section->id, 'position_id' => $position->id,
            'employment_status_id' => $employmentStatus->id, 'employee_class_id' => $employeeClass->id,
            'date_hired' => '2020-01-01', 'is_active' => true,
        ]);

        return $department;
    }
}
