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

class EmploymentStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_the_employment_status_index(): void
    {
        $user = User::factory()->create();
        $employmentStatus = EmploymentStatus::create([
            'status_code' => 'PROB',
            'status_name' => 'Probationary',
        ]);

        $response = $this->actingAs($user)->get(route('employment-statuses.index'));

        $response->assertOk();
        $response->assertSee($employmentStatus->status_name);
    }

    public function test_guest_is_redirected_from_the_employment_status_index(): void
    {
        $response = $this->get(route('employment-statuses.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_employment_status_can_be_created(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('employment-statuses.store'), [
            'status_code' => 'REG',
            'status_name' => 'Regular',
            'remarks' => 'Regular employee',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('employment-statuses.index'));
        $response->assertSessionHas('success', 'Employment status created successfully.');
        $this->assertDatabaseHas('employment_statuses', [
            'status_code' => 'REG',
            'status_name' => 'Regular',
        ]);
    }

    public function test_duplicate_employment_status_code_is_rejected(): void
    {
        $user = User::factory()->create();
        EmploymentStatus::create([
            'status_code' => 'REG',
            'status_name' => 'Regular',
        ]);

        $response = $this->actingAs($user)->post(route('employment-statuses.store'), [
            'status_code' => 'REG',
            'status_name' => 'Another',
        ]);

        $response->assertSessionHasErrors(['status_code']);
        $this->assertDatabaseCount('employment_statuses', 1);
    }

    public function test_employment_status_show_page_displays_details(): void
    {
        $user = User::factory()->create();
        $employmentStatus = EmploymentStatus::create([
            'status_code' => 'CON',
            'status_name' => 'Contractual',
            'remarks' => 'Contract',
        ]);

        $response = $this->actingAs($user)->get(route('employment-statuses.show', $employmentStatus));

        $response->assertOk();
        $response->assertSee($employmentStatus->status_code);
        $response->assertSee($employmentStatus->status_name);
    }

    public function test_employment_status_can_be_updated(): void
    {
        $user = User::factory()->create();
        $employmentStatus = EmploymentStatus::create([
            'status_code' => 'PROB',
            'status_name' => 'Probationary',
        ]);

        $response = $this->actingAs($user)->put(route('employment-statuses.update', $employmentStatus), [
            'status_code' => 'PROB-2',
            'status_name' => 'Probationary Updated',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('employment-statuses.index'));
        $response->assertSessionHas('success', 'Employment status updated successfully.');
        $this->assertDatabaseHas('employment_statuses', [
            'id' => $employmentStatus->id,
            'status_code' => 'PROB-2',
            'status_name' => 'Probationary Updated',
        ]);
    }

    public function test_employment_status_index_can_search_by_code_or_name(): void
    {
        $user = User::factory()->create();
        EmploymentStatus::create(['status_code' => 'REG', 'status_name' => 'Regular']);
        EmploymentStatus::create(['status_code' => 'CON', 'status_name' => 'Contractual']);

        $response = $this->actingAs($user)->get(route('employment-statuses.index', ['search' => 'regular']));

        $response->assertOk();
        $response->assertSee('Regular');
        $response->assertDontSee('Contractual');
    }

    public function test_employment_status_index_paginates_results(): void
    {
        $user = User::factory()->create();

        foreach (range(1, 12) as $index) {
            EmploymentStatus::create([
                'status_code' => 'STAT-' . str_pad((string) $index, 2, '0', STR_PAD_LEFT),
                'status_name' => 'Status ' . $index,
            ]);
        }

        $response = $this->actingAs($user)->get(route('employment-statuses.index'));

        $response->assertOk();
        $response->assertViewHas('employmentStatuses', function ($employmentStatuses) {
            return $employmentStatuses->count() === 10 && $employmentStatuses->hasPages();
        });
    }

    public function test_employment_status_can_be_archived_and_removed_from_the_active_list(): void
    {
        $user = User::factory()->create();
        $employmentStatus = EmploymentStatus::create([
            'status_code' => 'RES',
            'status_name' => 'Resigned',
        ]);

        $response = $this->actingAs($user)->post(route('employment-statuses.archive', $employmentStatus));

        $response->assertRedirect(route('employment-statuses.index'));
        $response->assertSessionHas('success', 'Employment status archived successfully.');
        $this->assertSoftDeleted('employment_statuses', ['id' => $employmentStatus->id]);

        $listResponse = $this->actingAs($user)->get(route('employment-statuses.index'));
        $listResponse->assertDontSee('Resigned');
    }

    public function test_employment_status_archive_is_blocked_when_active_employee_references_it(): void
    {
        $user = User::factory()->create();
        $employmentStatus = EmploymentStatus::create([
            'status_code' => 'PROJ',
            'status_name' => 'Project-Based',
        ]);
        $company = Company::create(['company_code' => 'C001', 'company_name' => 'Test Company']);
        $base = Base::create(['base_code' => 'B001', 'base_name' => 'Main Base']);
        $unit = Unit::create(['base_id' => $base->id, 'unit_code' => 'U001', 'unit_name' => 'Main Unit']);
        $department = Department::create(['unit_id' => $unit->id, 'department_code' => 'D001', 'department_name' => 'Main Department']);
        $section = Section::create(['department_id' => $department->id, 'section_code' => 'S001', 'section_name' => 'Main Section']);
        $position = Position::create(['section_id' => $section->id, 'position_code' => 'P001', 'position_name' => 'Main Position']);
        $employeeClass = EmployeeClass::create(['class_code' => 'RF', 'class_name' => 'Rank and File']);

        Employee::create([
            'employee_no' => 'EMP-001',
            'last_name' => 'Doe',
            'first_name' => 'Jane',
            'gender' => 'Female',
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

        $response = $this->actingAs($user)->from(route('employment-statuses.index'))->followingRedirects()
            ->post(route('employment-statuses.archive', $employmentStatus));

        $response
            ->assertSee('Cannot archive this employment status because active employees still reference it.');
        $this->assertDatabaseHas('employment_statuses', ['id' => $employmentStatus->id, 'deleted_at' => null]);
    }

    public function test_employment_status_can_be_restored(): void
    {
        $user = User::factory()->create();
        $employmentStatus = EmploymentStatus::create([
            'status_code' => 'REG',
            'status_name' => 'Regular',
        ]);
        $employmentStatus->delete();

        $response = $this->actingAs($user)->post(route('employment-statuses.restore', $employmentStatus->id));

        $response->assertRedirect(route('employment-statuses.index'));
        $response->assertSessionHas('success', 'Employment status restored successfully.');
        $this->assertDatabaseHas('employment_statuses', ['id' => $employmentStatus->id, 'deleted_at' => null]);
    }
}
