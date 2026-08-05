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

class UnitTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_the_unit_index(): void
    {
        $user = User::factory()->create();
        $unit = Unit::create(['unit_code' => 'UNIT-001', 'unit_name' => 'Main Unit']);

        $response = $this->actingAs($user)->get(route('units.index'));

        $response->assertOk();
        $response->assertSee($unit->unit_name);
    }

    public function test_guest_is_redirected_from_the_unit_index(): void
    {
        $response = $this->get(route('units.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_unit_can_be_created(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('units.store'), [
            'unit_code' => 'UNIT-001',
            'unit_name' => 'Main Unit',
            'remarks' => 'Primary unit',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('units.index'));
        $response->assertSessionHas('success', 'Unit created successfully.');
        $this->assertDatabaseHas('units', ['unit_code' => 'UNIT-001', 'unit_name' => 'Main Unit', 'base_id' => null]);
    }

    public function test_unit_can_be_updated(): void
    {
        $user = User::factory()->create();
        $unit = Unit::create(['unit_code' => 'UNIT-002', 'unit_name' => 'Old Unit']);

        $response = $this->actingAs($user)->put(route('units.update', $unit), [
            'unit_code' => 'UNIT-003',
            'unit_name' => 'Updated Unit',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('units.index'));
        $response->assertSessionHas('success', 'Unit updated successfully.');
        $this->assertDatabaseHas('units', ['id' => $unit->id, 'unit_code' => 'UNIT-003', 'unit_name' => 'Updated Unit']);
    }

    public function test_unit_show_page_displays_unit_details(): void
    {
        $user = User::factory()->create();
        $unit = Unit::create(['unit_code' => 'UNIT-004', 'unit_name' => 'Cebu Unit', 'remarks' => 'Operations']);

        $response = $this->actingAs($user)->get(route('units.show', $unit));

        $response->assertOk();
        $response->assertSee('UNIT-004');
        $response->assertSee('Cebu Unit');
        $response->assertSee('Operations');
    }

    public function test_unit_index_can_search_by_code_or_name(): void
    {
        $user = User::factory()->create();
        Unit::create(['unit_code' => 'ALPHA-01', 'unit_name' => 'Alpha Unit']);
        Unit::create(['unit_code' => 'BETA-01', 'unit_name' => 'Beta Unit']);

        $response = $this->actingAs($user)->get(route('units.index', ['search' => 'alpha']));

        $response->assertOk();
        $response->assertSee('Alpha Unit');
        $response->assertDontSee('Beta Unit');
    }

    public function test_unit_index_paginates_results(): void
    {
        $user = User::factory()->create();

        foreach (range(1, 12) as $index) {
            Unit::create(['unit_code' => 'UNIT-' . str_pad((string) $index, 2, '0', STR_PAD_LEFT), 'unit_name' => 'Unit ' . $index]);
        }

        $response = $this->actingAs($user)->get(route('units.index'));

        $response->assertOk();
        $response->assertViewHas('units', fn ($units) => $units->count() === 10 && $units->hasPages());
    }

    public function test_unit_can_be_archived_and_removed_from_active_list(): void
    {
        $user = User::factory()->create();
        $unit = Unit::create(['unit_code' => 'UNIT-005', 'unit_name' => 'Archive Me']);

        $response = $this->actingAs($user)->post(route('units.archive', $unit));

        $response->assertRedirect(route('units.index'));
        $response->assertSessionHas('success', 'Unit archived successfully.');
        $this->assertSoftDeleted('units', ['id' => $unit->id]);
        $this->actingAs($user)->get(route('units.index'))->assertDontSee('Archive Me');
    }

    public function test_unit_archive_is_blocked_when_active_employee_references_it(): void
    {
        $user = User::factory()->create();
        $unit = Unit::create(['unit_code' => 'UNIT-006', 'unit_name' => 'Protected Unit']);
        $base = Base::create(['base_code' => 'BASE-UNIT', 'base_name' => 'Employee Base']);
        $company = Company::create(['company_code' => 'CMP-UNIT', 'company_name' => 'Unit Company']);
        $department = Department::create(['unit_id' => $unit->id, 'department_code' => 'DEP-UNIT', 'department_name' => 'Unit Department']);
        $section = Section::create(['department_id' => $department->id, 'section_code' => 'SEC-UNIT', 'section_name' => 'Unit Section']);
        $position = Position::create(['section_id' => $section->id, 'position_code' => 'POS-UNIT', 'position_name' => 'Unit Position']);
        $employmentStatus = EmploymentStatus::create(['status_code' => 'STAT-UNIT', 'status_name' => 'Regular']);
        $employeeClass = EmployeeClass::create(['class_code' => 'CLASS-UNIT', 'class_name' => 'Regular']);

        Employee::create([
            'employee_no' => 'E-UNIT-001', 'last_name' => 'Doe', 'first_name' => 'Jane', 'gender' => 'Female',
            'civil_status' => 'Single', 'birth_date' => '1990-01-01', 'company_id' => $company->id,
            'base_id' => $base->id, 'unit_id' => $unit->id, 'department_id' => $department->id,
            'section_id' => $section->id, 'position_id' => $position->id, 'employment_status_id' => $employmentStatus->id,
            'employee_class_id' => $employeeClass->id, 'date_hired' => '2020-01-01', 'is_active' => true,
        ]);

        $response = $this->actingAs($user)->post(route('units.archive', $unit));

        $response->assertSessionHasErrors(['unit']);
        $this->assertDatabaseHas('units', ['id' => $unit->id, 'deleted_at' => null]);
    }

    public function test_unit_can_be_restored(): void
    {
        $user = User::factory()->create();
        $unit = Unit::create(['unit_code' => 'UNIT-007', 'unit_name' => 'Restore Me']);
        $unit->delete();

        $response = $this->actingAs($user)->post(route('units.restore', $unit->id));

        $response->assertRedirect(route('units.index'));
        $response->assertSessionHas('success', 'Unit restored successfully.');
        $this->assertDatabaseHas('units', ['id' => $unit->id, 'deleted_at' => null]);
    }

    public function test_unit_validation_returns_errors_and_preserves_input(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->from(route('units.create'))->post(route('units.store'), [
            'unit_code' => '', 'unit_name' => '',
        ]);

        $response->assertRedirect(route('units.create'));
        $response->assertSessionHasErrors(['unit_code', 'unit_name']);
        $response->assertSessionHasInput('unit_code', '');
    }
}
