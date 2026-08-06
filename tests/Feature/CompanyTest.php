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

class CompanyTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_the_company_index(): void
    {
        $user = User::factory()->create();
        $company = Company::create([
            'company_code' => 'ABC-001',
            'company_name' => 'Boie Solutions',
        ]);

        $response = $this->actingAs($user)->get(route('companies.index'));

        $response->assertOk();
        $response->assertSee($company->company_name);
    }

    public function test_guest_is_redirected_from_the_company_index(): void
    {
        $response = $this->get(route('companies.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_company_can_be_created(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('companies.store'), [
            'company_code' => 'ABC-001',
            'company_name' => 'Boie Solutions',
            'contact_person' => 'John Doe',
            'contact_number' => '09171234567',
            'email' => 'info@boie.com',
            'address' => 'Cebu City',
            'remarks' => 'Primary account',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('companies.index'));
        $response->assertSessionHas('success', 'Company created successfully.');
        $this->assertDatabaseHas('companies', [
            'company_code' => 'ABC-001',
            'company_name' => 'Boie Solutions',
        ]);
    }

    public function test_duplicate_company_code_is_rejected(): void
    {
        $user = User::factory()->create();
        Company::create([
            'company_code' => 'ABC-001',
            'company_name' => 'Boie Solutions',
        ]);

        $response = $this->actingAs($user)->post(route('companies.store'), [
            'company_code' => 'ABC-001',
            'company_name' => 'Another Company',
        ]);

        $response->assertSessionHasErrors(['company_code']);
        $this->assertDatabaseCount('companies', 1);
    }

    public function test_company_show_page_displays_company_details(): void
    {
        $user = User::factory()->create();
        $company = Company::create([
            'company_code' => 'ABC-002',
            'company_name' => 'Boie Logistics',
            'contact_person' => 'Jane Doe',
            'address' => 'Manila',
            'remarks' => 'Main office',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('companies.show', $company));

        $response->assertOk();
        $response->assertSee($company->company_code);
        $response->assertSee($company->company_name);
        $response->assertSee('Jane Doe');
    }

    public function test_company_can_be_updated(): void
    {
        $user = User::factory()->create();
        $company = Company::create([
            'company_code' => 'ABC-003',
            'company_name' => 'Old Name',
        ]);

        $response = $this->actingAs($user)->put(route('companies.update', $company), [
            'company_code' => 'ABC-004',
            'company_name' => 'New Name',
            'contact_person' => 'Updated Person',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('companies.index'));
        $response->assertSessionHas('success', 'Company updated successfully.');
        $this->assertDatabaseHas('companies', [
            'id' => $company->id,
            'company_code' => 'ABC-004',
            'company_name' => 'New Name',
        ]);
    }

    public function test_company_index_can_search_by_code_or_name(): void
    {
        $user = User::factory()->create();
        Company::create(['company_code' => 'ALPHA-01', 'company_name' => 'Alpha Corp']);
        Company::create(['company_code' => 'BETA-01', 'company_name' => 'Beta Corp']);

        $response = $this->actingAs($user)->get(route('companies.index', ['search' => 'alpha']));

        $response->assertOk();
        $response->assertSee('Alpha Corp');
        $response->assertDontSee('Beta Corp');
    }

    public function test_company_index_paginates_results(): void
    {
        $user = User::factory()->create();

        foreach (range(1, 12) as $index) {
            Company::create([
                'company_code' => 'CMP-' . str_pad((string) $index, 2, '0', STR_PAD_LEFT),
                'company_name' => 'Company ' . $index,
            ]);
        }

        $response = $this->actingAs($user)->get(route('companies.index'));

        $response->assertOk();
        $response->assertViewHas('companies', function ($companies) {
            return $companies->count() === 10 && $companies->hasPages();
        });
    }

    public function test_company_can_be_archived_and_removed_from_the_active_list(): void
    {
        $user = User::factory()->create();
        $company = Company::create([
            'company_code' => 'ABC-005',
            'company_name' => 'Archive Me',
        ]);

        $response = $this->actingAs($user)->post(route('companies.archive', $company));

        $response->assertRedirect(route('companies.index'));
        $response->assertSessionHas('success', 'Company archived successfully.');
        $this->assertSoftDeleted('companies', ['id' => $company->id]);

        $listResponse = $this->actingAs($user)->get(route('companies.index'));
        $listResponse->assertDontSee('Archive Me');
    }

    public function test_company_archive_is_blocked_when_active_employee_references_it(): void
    {
        $user = User::factory()->create();
        $company = Company::create([
            'company_code' => 'ABC-006',
            'company_name' => 'Protected Company',
        ]);
        $base = Base::create(['base_code' => 'BASE-01', 'base_name' => 'Main Base']);
        $unit = Unit::create(['base_id' => $base->id, 'unit_code' => 'UNIT-01', 'unit_name' => 'Main Unit']);
        $department = Department::create(['unit_id' => $unit->id, 'department_code' => 'DEP-01', 'department_name' => 'Main Department']);
        $section = Section::create(['department_id' => $department->id, 'section_code' => 'SEC-01', 'section_name' => 'Main Section']);
        $position = Position::create(['section_id' => $section->id, 'position_code' => 'POS-01', 'position_name' => 'Main Position']);
        $employmentStatus = EmploymentStatus::create(['status_code' => 'STAT-01', 'status_name' => 'Regular']);
        $employeeClass = EmployeeClass::create(['class_code' => 'CLASS-01', 'class_name' => 'Regular']);

        Employee::create([
            'employee_no' => 'E001',
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

        $response = $this->actingAs($user)->from(route('companies.index'))->followingRedirects()
            ->post(route('companies.archive', $company));

        $response
            ->assertSee('Cannot archive this company because active employees still reference it.');
        $this->assertDatabaseHas('companies', ['id' => $company->id, 'deleted_at' => null]);
    }

    public function test_company_can_be_restored(): void
    {
        $user = User::factory()->create();
        $company = Company::create([
            'company_code' => 'ABC-007',
            'company_name' => 'Restore Me',
        ]);
        $company->delete();

        $response = $this->actingAs($user)->post(route('companies.restore', $company->id));

        $response->assertRedirect(route('companies.index'));
        $response->assertSessionHas('success', 'Company restored successfully.');
        $this->assertDatabaseHas('companies', ['id' => $company->id, 'deleted_at' => null]);
    }
}
