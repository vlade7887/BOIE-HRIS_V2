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

class BaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_the_base_index(): void
    {
        $user = User::factory()->create();
        $base = Base::create(['base_code' => 'BASE-001', 'base_name' => 'Main Base']);

        $response = $this->actingAs($user)->get(route('bases.index'));

        $response->assertOk();
        $response->assertSee($base->base_name);
    }

    public function test_guest_is_redirected_from_the_base_index(): void
    {
        $response = $this->get(route('bases.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_base_can_be_created(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('bases.store'), [
            'base_code' => 'BASE-001',
            'base_name' => 'Main Base',
            'remarks' => 'Primary location',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('bases.index'));
        $response->assertSessionHas('success', 'Base created successfully.');
        $this->assertDatabaseHas('bases', ['base_code' => 'BASE-001', 'base_name' => 'Main Base']);
    }

    public function test_base_can_be_updated(): void
    {
        $user = User::factory()->create();
        $base = Base::create(['base_code' => 'BASE-002', 'base_name' => 'Old Base']);

        $response = $this->actingAs($user)->put(route('bases.update', $base), [
            'base_code' => 'BASE-003',
            'base_name' => 'Updated Base',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('bases.index'));
        $response->assertSessionHas('success', 'Base updated successfully.');
        $this->assertDatabaseHas('bases', ['id' => $base->id, 'base_code' => 'BASE-003', 'base_name' => 'Updated Base']);
    }

    public function test_base_show_page_displays_base_details(): void
    {
        $user = User::factory()->create();
        $base = Base::create(['base_code' => 'BASE-004', 'base_name' => 'Cebu Base', 'remarks' => 'Operations']);

        $response = $this->actingAs($user)->get(route('bases.show', $base));

        $response->assertOk();
        $response->assertSee('BASE-004');
        $response->assertSee('Cebu Base');
        $response->assertSee('Operations');
    }

    public function test_base_index_can_search_by_code_or_name(): void
    {
        $user = User::factory()->create();
        Base::create(['base_code' => 'ALPHA-01', 'base_name' => 'Alpha Base']);
        Base::create(['base_code' => 'BETA-01', 'base_name' => 'Beta Base']);

        $response = $this->actingAs($user)->get(route('bases.index', ['search' => 'alpha']));

        $response->assertOk();
        $response->assertSee('Alpha Base');
        $response->assertDontSee('Beta Base');
    }

    public function test_base_index_paginates_results(): void
    {
        $user = User::factory()->create();

        foreach (range(1, 12) as $index) {
            Base::create(['base_code' => 'BASE-' . str_pad((string) $index, 2, '0', STR_PAD_LEFT), 'base_name' => 'Base ' . $index]);
        }

        $response = $this->actingAs($user)->get(route('bases.index'));

        $response->assertOk();
        $response->assertViewHas('bases', fn ($bases) => $bases->count() === 10 && $bases->hasPages());
    }

    public function test_base_can_be_archived_and_removed_from_active_list(): void
    {
        $user = User::factory()->create();
        $base = Base::create(['base_code' => 'BASE-005', 'base_name' => 'Archive Me']);

        $response = $this->actingAs($user)->post(route('bases.archive', $base));

        $response->assertRedirect(route('bases.index'));
        $response->assertSessionHas('success', 'Base archived successfully.');
        $this->assertSoftDeleted('bases', ['id' => $base->id]);
        $this->actingAs($user)->get(route('bases.index'))->assertDontSee('Archive Me');
    }

    public function test_base_archive_is_blocked_when_active_employee_references_it(): void
    {
        $user = User::factory()->create();
        $base = Base::create(['base_code' => 'BASE-006', 'base_name' => 'Protected Base']);
        $company = Company::create(['company_code' => 'CMP-BASE', 'company_name' => 'Base Company']);
        $unit = Unit::create(['base_id' => $base->id, 'unit_code' => 'UNIT-BASE', 'unit_name' => 'Base Unit']);
        $department = Department::create(['unit_id' => $unit->id, 'department_code' => 'DEP-BASE', 'department_name' => 'Base Department']);
        $section = Section::create(['department_id' => $department->id, 'section_code' => 'SEC-BASE', 'section_name' => 'Base Section']);
        $position = Position::create(['section_id' => $section->id, 'position_code' => 'POS-BASE', 'position_name' => 'Base Position']);
        $employmentStatus = EmploymentStatus::create(['status_code' => 'STAT-BASE', 'status_name' => 'Regular']);
        $employeeClass = EmployeeClass::create(['class_code' => 'CLASS-BASE', 'class_name' => 'Regular']);

        Employee::create([
            'employee_no' => 'E-BASE-001', 'last_name' => 'Doe', 'first_name' => 'Jane', 'gender' => 'Female',
            'civil_status' => 'Single', 'birth_date' => '1990-01-01', 'company_id' => $company->id,
            'base_id' => $base->id, 'unit_id' => $unit->id, 'department_id' => $department->id,
            'section_id' => $section->id, 'position_id' => $position->id, 'employment_status_id' => $employmentStatus->id,
            'employee_class_id' => $employeeClass->id, 'date_hired' => '2020-01-01', 'is_active' => true,
        ]);

        $response = $this->actingAs($user)->post(route('bases.archive', $base));

        $response->assertSessionHasErrors(['base']);
        $this->assertDatabaseHas('bases', ['id' => $base->id, 'deleted_at' => null]);
    }

    public function test_base_can_be_restored(): void
    {
        $user = User::factory()->create();
        $base = Base::create(['base_code' => 'BASE-007', 'base_name' => 'Restore Me']);
        $base->delete();

        $response = $this->actingAs($user)->post(route('bases.restore', $base->id));

        $response->assertRedirect(route('bases.index'));
        $response->assertSessionHas('success', 'Base restored successfully.');
        $this->assertDatabaseHas('bases', ['id' => $base->id, 'deleted_at' => null]);
    }

    public function test_base_validation_returns_errors_and_preserves_input(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->from(route('bases.create'))->post(route('bases.store'), [
            'base_code' => '', 'base_name' => '',
        ]);

        $response->assertRedirect(route('bases.create'));
        $response->assertSessionHasErrors(['base_code', 'base_name']);
        $response->assertSessionHasInput('base_code', '');
    }
}
