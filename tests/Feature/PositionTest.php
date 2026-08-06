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

class PositionTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_the_position_index(): void
    {
        $user = User::factory()->create();
        $position = Position::create(['position_code' => 'POS-001', 'position_name' => 'IT Staff']);

        $this->actingAs($user)->get(route('positions.index'))
            ->assertOk()->assertSee($position->position_name);
    }

    public function test_guest_is_redirected_from_the_position_index(): void
    {
        $this->get(route('positions.index'))->assertRedirect(route('login'));
    }

    public function test_position_can_be_created_without_a_section(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('positions.store'), [
            'position_code' => 'POS-001', 'position_name' => 'Warehouse Staff', 'remarks' => 'Independent position', 'is_active' => true,
        ])->assertRedirect(route('positions.index'))->assertSessionHas('success', 'Position created successfully.');

        $this->assertDatabaseHas('positions', ['position_code' => 'POS-001', 'section_id' => null]);
    }

    public function test_duplicate_position_code_is_rejected(): void
    {
        $user = User::factory()->create();
        Position::create(['position_code' => 'POS-002', 'position_name' => 'IT Staff']);

        $this->actingAs($user)->from(route('positions.create'))
            ->post(route('positions.store'), ['position_code' => 'POS-002', 'position_name' => 'Another Position'])
            ->assertRedirect(route('positions.create'))->assertSessionHasErrors(['position_code']);
    }

    public function test_position_can_be_updated(): void
    {
        $user = User::factory()->create();
        $position = Position::create(['position_code' => 'POS-003', 'position_name' => 'Old Name']);

        $this->actingAs($user)->put(route('positions.update', $position), [
            'position_code' => 'POS-004', 'position_name' => 'HR Staff', 'is_active' => true,
        ])->assertRedirect(route('positions.index'))->assertSessionHas('success', 'Position updated successfully.');

        $this->assertDatabaseHas('positions', ['id' => $position->id, 'position_code' => 'POS-004', 'position_name' => 'HR Staff']);
    }

    public function test_position_show_page_displays_details(): void
    {
        $user = User::factory()->create();
        $position = Position::create(['position_code' => 'POS-005', 'position_name' => 'Operations Staff']);

        $this->actingAs($user)->get(route('positions.show', $position))
            ->assertOk()
            ->assertSee('POS-005')
            ->assertSee('Operations Staff')
            ->assertSee('Edit')
            ->assertSee('Back')
            ->assertSee(route('positions.edit', $position))
            ->assertSee(route('positions.index'));
    }

    public function test_position_index_can_search_by_code_or_name(): void
    {
        $user = User::factory()->create();
        Position::create(['position_code' => 'ALPHA-01', 'position_name' => 'Alpha Staff']);
        Position::create(['position_code' => 'BETA-01', 'position_name' => 'Beta Staff']);

        $this->actingAs($user)->get(route('positions.index', ['search' => 'alpha']))
            ->assertOk()->assertSee('Alpha Staff')->assertDontSee('Beta Staff');
    }

    public function test_position_index_paginates_results(): void
    {
        $user = User::factory()->create();
        foreach (range(1, 12) as $index) {
            Position::create(['position_code' => 'POS-' . $index, 'position_name' => 'Position ' . $index]);
        }

        $this->actingAs($user)->get(route('positions.index'))->assertViewHas('positions', function ($positions) {
            return $positions->count() === 10 && $positions->hasPages();
        });
    }

    public function test_position_can_be_archived_and_restored_with_flash_messages(): void
    {
        $user = User::factory()->create();
        $position = Position::create(['position_code' => 'POS-006', 'position_name' => 'Archive Me']);

        $this->actingAs($user)->post(route('positions.archive', $position))
            ->assertRedirect(route('positions.index'))->assertSessionHas('success', 'Position archived successfully.');
        $this->assertSoftDeleted('positions', ['id' => $position->id]);

        $this->actingAs($user)->get(route('positions.index', ['view' => 'archived']))
            ->assertOk()->assertSee('Archive Me')->assertSee('Restore');

        $this->actingAs($user)->post(route('positions.restore', $position->id))
            ->assertRedirect(route('positions.index'))->assertSessionHas('success', 'Position restored successfully.');
        $this->assertDatabaseHas('positions', ['id' => $position->id, 'deleted_at' => null]);
    }

    public function test_position_archive_is_blocked_when_active_employee_references_it(): void
    {
        $user = User::factory()->create();
        $position = $this->createPositionWithActiveEmployee();

        $this->actingAs($user)->post(route('positions.archive', $position))
            ->assertSessionHasErrors(['position']);
        $this->assertDatabaseHas('positions', ['id' => $position->id, 'deleted_at' => null]);
    }

    public function test_position_validation_returns_summary_and_preserves_old_input(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->from(route('positions.create'))
            ->post(route('positions.store'), ['position_code' => 'BAD', 'position_name' => ''])
            ->assertRedirect(route('positions.create'))->assertSessionHasErrors(['position_name']);
    }

    private function createPositionWithActiveEmployee(): Position
    {
        $company = Company::create(['company_code' => 'CMP-P', 'company_name' => 'Position Company']);
        $base = Base::create(['base_code' => 'BASE-P', 'base_name' => 'Position Base']);
        $unit = Unit::create(['base_id' => $base->id, 'unit_code' => 'UNIT-P', 'unit_name' => 'Position Unit']);
        $department = Department::create(['unit_id' => $unit->id, 'department_code' => 'DEP-P', 'department_name' => 'Position Department']);
        $section = Section::create(['department_id' => $department->id, 'section_code' => 'SEC-P', 'section_name' => 'Position Section']);
        $position = Position::create(['section_id' => $section->id, 'position_code' => 'POS-P', 'position_name' => 'Protected Position']);
        $employmentStatus = EmploymentStatus::create(['status_code' => 'STAT-P', 'status_name' => 'Regular']);
        $employeeClass = EmployeeClass::create(['class_code' => 'CLASS-P', 'class_name' => 'Regular']);

        Employee::create([
            'employee_no' => 'EMP-P', 'last_name' => 'Doe', 'first_name' => 'Jane', 'gender' => 'Female', 'civil_status' => 'Single',
            'birth_date' => '1990-01-01', 'company_id' => $company->id, 'base_id' => $base->id, 'unit_id' => $unit->id,
            'department_id' => $department->id, 'section_id' => $section->id, 'position_id' => $position->id,
            'employment_status_id' => $employmentStatus->id, 'employee_class_id' => $employeeClass->id, 'date_hired' => '2020-01-01', 'is_active' => true,
        ]);

        return $position;
    }
}
