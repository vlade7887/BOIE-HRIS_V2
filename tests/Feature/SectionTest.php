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

class SectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_the_section_index(): void
    {
        $user = User::factory()->create();
        $section = Section::create(['section_code' => 'SEC-001', 'section_name' => 'Human Resources']);

        $this->actingAs($user)->get(route('sections.index'))
            ->assertOk()->assertSee($section->section_name);
    }

    public function test_guest_is_redirected_from_the_section_index(): void
    {
        $this->get(route('sections.index'))->assertRedirect(route('login'));
    }

    public function test_section_can_be_created_without_a_department(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('sections.store'), [
            'section_code' => 'SEC-001', 'section_name' => 'Human Resources', 'remarks' => 'Independent section', 'is_active' => true,
        ])->assertRedirect(route('sections.index'))->assertSessionHas('success', 'Section created successfully.');

        $this->assertDatabaseHas('sections', ['section_code' => 'SEC-001', 'department_id' => null]);
    }

    public function test_section_can_be_updated(): void
    {
        $user = User::factory()->create();
        $section = Section::create(['section_code' => 'SEC-002', 'section_name' => 'Old Name']);

        $this->actingAs($user)->put(route('sections.update', $section), [
            'section_code' => 'SEC-003', 'section_name' => 'New Name', 'is_active' => true,
        ])->assertRedirect(route('sections.index'))->assertSessionHas('success', 'Section updated successfully.');

        $this->assertDatabaseHas('sections', ['id' => $section->id, 'section_code' => 'SEC-003', 'section_name' => 'New Name']);
    }

    public function test_section_show_page_displays_details(): void
    {
        $user = User::factory()->create();
        $section = Section::create(['section_code' => 'SEC-004', 'section_name' => 'Operations']);

        $this->actingAs($user)->get(route('sections.show', $section))
            ->assertOk()
            ->assertSee('SEC-004')
            ->assertSee('Operations')
            ->assertSee('Edit')
            ->assertSee('Back')
            ->assertSee(route('sections.edit', $section))
            ->assertSee(route('sections.index'));
    }

    public function test_section_index_can_search_by_code_or_name(): void
    {
        $user = User::factory()->create();
        Section::create(['section_code' => 'ALPHA-01', 'section_name' => 'Alpha Section']);
        Section::create(['section_code' => 'BETA-01', 'section_name' => 'Beta Section']);

        $this->actingAs($user)->get(route('sections.index', ['search' => 'alpha']))
            ->assertOk()->assertSee('Alpha Section')->assertDontSee('Beta Section');
    }

    public function test_section_index_paginates_results(): void
    {
        $user = User::factory()->create();
        foreach (range(1, 12) as $index) {
            Section::create(['section_code' => 'SEC-' . $index, 'section_name' => 'Section ' . $index]);
        }

        $this->actingAs($user)->get(route('sections.index'))->assertViewHas('sections', function ($sections) {
            return $sections->count() === 10 && $sections->hasPages();
        });
    }

    public function test_section_can_be_archived_and_restored_with_flash_messages(): void
    {
        $user = User::factory()->create();
        $section = Section::create(['section_code' => 'SEC-005', 'section_name' => 'Archive Me']);

        $this->actingAs($user)->post(route('sections.archive', $section))
            ->assertRedirect(route('sections.index'))->assertSessionHas('success', 'Section archived successfully.');
        $this->assertSoftDeleted('sections', ['id' => $section->id]);

        $this->actingAs($user)->get(route('sections.index', ['view' => 'archived']))
            ->assertOk()->assertSee('Archive Me')->assertSee('Restore');

        $this->actingAs($user)->post(route('sections.restore', $section->id))
            ->assertRedirect(route('sections.index'))->assertSessionHas('success', 'Section restored successfully.');
        $this->assertDatabaseHas('sections', ['id' => $section->id, 'deleted_at' => null]);
    }

    public function test_section_archive_is_blocked_when_active_employee_references_it(): void
    {
        $user = User::factory()->create();
        $section = $this->createSectionWithActiveEmployee();

        $this->actingAs($user)->post(route('sections.archive', $section))
            ->assertSessionHasErrors(['section']);
        $this->assertDatabaseHas('sections', ['id' => $section->id, 'deleted_at' => null]);
    }

    public function test_section_validation_returns_summary_and_preserves_old_input(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->from(route('sections.create'))
            ->post(route('sections.store'), ['section_code' => 'BAD', 'section_name' => ''])
            ->assertRedirect(route('sections.create'))->assertSessionHasErrors(['section_name']);
    }

    private function createSectionWithActiveEmployee(): Section
    {
        $company = Company::create(['company_code' => 'CMP-S', 'company_name' => 'Section Company']);
        $base = Base::create(['base_code' => 'BASE-S', 'base_name' => 'Section Base']);
        $unit = Unit::create(['base_id' => $base->id, 'unit_code' => 'UNIT-S', 'unit_name' => 'Section Unit']);
        $department = Department::create(['unit_id' => $unit->id, 'department_code' => 'DEP-S', 'department_name' => 'Section Department']);
        $section = Section::create(['department_id' => $department->id, 'section_code' => 'SEC-P', 'section_name' => 'Protected Section']);
        $position = Position::create(['section_id' => $section->id, 'position_code' => 'POS-S', 'position_name' => 'Section Position']);
        $employmentStatus = EmploymentStatus::create(['status_code' => 'STAT-S', 'status_name' => 'Regular']);
        $employeeClass = EmployeeClass::create(['class_code' => 'CLASS-S', 'class_name' => 'Regular']);

        Employee::create([
            'employee_no' => 'EMP-S', 'last_name' => 'Doe', 'first_name' => 'Jane', 'gender' => 'Female', 'civil_status' => 'Single',
            'birth_date' => '1990-01-01', 'company_id' => $company->id, 'base_id' => $base->id, 'unit_id' => $unit->id,
            'department_id' => $department->id, 'section_id' => $section->id, 'position_id' => $position->id,
            'employment_status_id' => $employmentStatus->id, 'employee_class_id' => $employeeClass->id, 'date_hired' => '2020-01-01', 'is_active' => true,
        ]);

        return $section;
    }
}
