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

class EmployeeClassTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_the_employee_class_index(): void
    {
        $user = User::factory()->create();
        $employeeClass = EmployeeClass::create([
            'class_code' => 'RF',
            'class_name' => 'Rank and File',
        ]);

        $response = $this->actingAs($user)->get(route('employee-classes.index'));

        $response->assertOk();
        $response->assertSee($employeeClass->class_name);
    }

    public function test_guest_is_redirected_from_the_employee_class_index(): void
    {
        $response = $this->get(route('employee-classes.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_employee_class_can_be_created(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('employee-classes.store'), [
            'class_code' => 'SUP',
            'class_name' => 'Supervisory',
            'remarks' => 'Supervisory level',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('employee-classes.index'));
        $response->assertSessionHas('success', 'Employee class created successfully.');
        $this->assertDatabaseHas('employee_classes', [
            'class_code' => 'SUP',
            'class_name' => 'Supervisory',
        ]);
    }

    public function test_duplicate_employee_class_code_is_rejected(): void
    {
        $user = User::factory()->create();
        EmployeeClass::create([
            'class_code' => 'SUP',
            'class_name' => 'Supervisory',
        ]);

        $response = $this->actingAs($user)->post(route('employee-classes.store'), [
            'class_code' => 'SUP',
            'class_name' => 'Another',
        ]);

        $response->assertSessionHasErrors(['class_code']);
        $this->assertDatabaseCount('employee_classes', 1);
    }

    public function test_employee_class_show_page_displays_details(): void
    {
        $user = User::factory()->create();
        $employeeClass = EmployeeClass::create([
            'class_code' => 'CONF',
            'class_name' => 'Confidential',
            'remarks' => 'Confidential',
        ]);

        $response = $this->actingAs($user)->get(route('employee-classes.show', $employeeClass));

        $response->assertOk();
        $response->assertSee($employeeClass->class_code);
        $response->assertSee($employeeClass->class_name);
    }

    public function test_employee_class_can_be_updated(): void
    {
        $user = User::factory()->create();
        $employeeClass = EmployeeClass::create([
            'class_code' => 'RF',
            'class_name' => 'Rank and File',
        ]);

        $response = $this->actingAs($user)->put(route('employee-classes.update', $employeeClass), [
            'class_code' => 'RF-2',
            'class_name' => 'Rank and File Updated',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('employee-classes.index'));
        $response->assertSessionHas('success', 'Employee class updated successfully.');
        $this->assertDatabaseHas('employee_classes', [
            'id' => $employeeClass->id,
            'class_code' => 'RF-2',
            'class_name' => 'Rank and File Updated',
        ]);
    }

    public function test_employee_class_index_can_search_by_code_or_name(): void
    {
        $user = User::factory()->create();
        EmployeeClass::create(['class_code' => 'RF', 'class_name' => 'Rank and File']);
        EmployeeClass::create(['class_code' => 'SUP', 'class_name' => 'Supervisory']);

        $response = $this->actingAs($user)->get(route('employee-classes.index', ['search' => 'super']));

        $response->assertOk();
        $response->assertSee('Supervisory');
        $response->assertDontSee('Rank and File');
    }

    public function test_employee_class_index_paginates_results(): void
    {
        $user = User::factory()->create();

        foreach (range(1, 12) as $index) {
            EmployeeClass::create([
                'class_code' => 'CLS-' . str_pad((string) $index, 2, '0', STR_PAD_LEFT),
                'class_name' => 'Class ' . $index,
            ]);
        }

        $response = $this->actingAs($user)->get(route('employee-classes.index'));

        $response->assertOk();
        $response->assertViewHas('employeeClasses', function ($employeeClasses) {
            return $employeeClasses->count() === 10 && $employeeClasses->hasPages();
        });
    }

    public function test_employee_class_can_be_archived_and_removed_from_the_active_list(): void
    {
        $user = User::factory()->create();
        $employeeClass = EmployeeClass::create([
            'class_code' => 'EXEC',
            'class_name' => 'Executive',
        ]);

        $response = $this->actingAs($user)->post(route('employee-classes.archive', $employeeClass));

        $response->assertRedirect(route('employee-classes.index'));
        $response->assertSessionHas('success', 'Employee class archived successfully.');
        $this->assertSoftDeleted('employee_classes', ['id' => $employeeClass->id]);

        $listResponse = $this->actingAs($user)->get(route('employee-classes.index'));
        $listResponse->assertDontSee('Executive');
    }

    public function test_employee_class_archive_is_blocked_when_active_employee_references_it(): void
    {
        $user = User::factory()->create();
        $employeeClass = EmployeeClass::create([
            'class_code' => 'MGR',
            'class_name' => 'Managerial 1',
        ]);
        $company = Company::create(['company_code' => 'C002', 'company_name' => 'Test Company 2']);
        $base = Base::create(['base_code' => 'B002', 'base_name' => 'Main Base 2']);
        $unit = Unit::create(['base_id' => $base->id, 'unit_code' => 'U002', 'unit_name' => 'Main Unit 2']);
        $department = Department::create(['unit_id' => $unit->id, 'department_code' => 'D002', 'department_name' => 'Main Department 2']);
        $section = Section::create(['department_id' => $department->id, 'section_code' => 'S002', 'section_name' => 'Main Section 2']);
        $position = Position::create(['section_id' => $section->id, 'position_code' => 'P002', 'position_name' => 'Main Position 2']);
        $employmentStatus = EmploymentStatus::create(['status_code' => 'REG', 'status_name' => 'Regular']);

        Employee::create([
            'employee_no' => 'EMP-002',
            'last_name' => 'Smith',
            'first_name' => 'John',
            'gender' => 'Male',
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

        $response = $this->actingAs($user)->post(route('employee-classes.archive', $employeeClass));

        $response->assertSessionHasErrors(['employeeClass']);
        $this->assertDatabaseHas('employee_classes', ['id' => $employeeClass->id, 'deleted_at' => null]);
    }

    public function test_employee_class_can_be_restored(): void
    {
        $user = User::factory()->create();
        $employeeClass = EmployeeClass::create([
            'class_code' => 'RF',
            'class_name' => 'Rank and File',
        ]);
        $employeeClass->delete();

        $response = $this->actingAs($user)->post(route('employee-classes.restore', $employeeClass->id));

        $response->assertRedirect(route('employee-classes.index'));
        $response->assertSessionHas('success', 'Employee class restored successfully.');
        $this->assertDatabaseHas('employee_classes', ['id' => $employeeClass->id, 'deleted_at' => null]);
    }
}
