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

class EmployeeCreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_open_the_create_page_with_seeded_master_data(): void
    {
        $this->seed();
        $user = User::where('email', 'test@example.com')->firstOrFail();

        $this->actingAs($user)
            ->get(route('employees.create'))
            ->assertOk()
            ->assertSee([
                'BOIE Incorporated',
                'Main Office',
                'Corporate Services',
                'Human Resources',
                'HR Operations',
                'HR Staff',
                'Probationary',
                'Rank and File',
            ]);
    }

    public function test_missing_required_data_returns_visible_validation_errors(): void
    {
        $this->seed();
        $user = User::where('email', 'test@example.com')->firstOrFail();

        $response = $this->followingRedirects()
            ->actingAs($user)
            ->from(route('employees.create'))
            ->post(route('employees.store'), []);

        $response->assertOk()
            ->assertSee([
                'Please correct the highlighted fields.',
                'The employee no field is required.',
                'The company id field is required.',
            ]);
    }

    public function test_valid_employee_can_be_created_with_seeded_master_data_and_supporting_records(): void
    {
        $this->seed();
        $user = User::where('email', 'test@example.com')->firstOrFail();

        $response = $this->actingAs($user)->post(route('employees.store'), $this->employeePayload());

        $employee = Employee::where('employee_no', 'DEV-EMP-001')->firstOrFail();

        $response->assertRedirect(route('employees.show', $employee));
        $response->assertSessionHas('success', 'Employee created successfully.');
        $this->assertDatabaseHas('employee_contacts', ['employee_id' => $employee->id]);
        $this->assertDatabaseHas('employee_addresses', ['employee_id' => $employee->id]);
        $this->assertDatabaseHas('employee_government_ids', ['employee_id' => $employee->id]);
    }

    public function test_employee_creation_without_nationality_defaults_to_filipino(): void
    {
        $this->seed();
        $user = User::where('email', 'test@example.com')->firstOrFail();

        $this->actingAs($user)->post(route('employees.store'), $this->employeePayload());

        $employee = Employee::latest()->firstOrFail();

        $this->assertSame('Filipino', $employee->nationality);
    }

    public function test_employee_creation_with_null_nationality_defaults_to_filipino(): void
    {
        $this->seed();
        $user = User::where('email', 'test@example.com')->firstOrFail();

        $this->actingAs($user)->post(route('employees.store'), $this->employeePayload(['nationality' => null]));

        $employee = Employee::latest()->firstOrFail();

        $this->assertSame('Filipino', $employee->nationality);
    }

    public function test_employee_creation_with_whitespace_nationality_defaults_to_filipino(): void
    {
        $this->seed();
        $user = User::where('email', 'test@example.com')->firstOrFail();

        $this->actingAs($user)->post(route('employees.store'), $this->employeePayload(['nationality' => '   ']));

        $employee = Employee::latest()->firstOrFail();

        $this->assertSame('Filipino', $employee->nationality);
    }

    public function test_employee_creation_with_supplied_nationality_preserves_value(): void
    {
        $this->seed();
        $user = User::where('email', 'test@example.com')->firstOrFail();

        $this->actingAs($user)->post(route('employees.store'), $this->employeePayload(['nationality' => 'American']));

        $employee = Employee::latest()->firstOrFail();

        $this->assertSame('American', $employee->nationality);
    }

    public function test_master_data_seeding_is_idempotent(): void
    {
        $this->seed();
        $this->seed();

        $this->assertSame(1, Company::where('company_code', 'DEV-BOIE')->count());
        $this->assertSame(1, Base::where('base_code', 'DEV-MAIN')->count());
        $this->assertSame(1, Unit::where('unit_code', 'DEV-CORP')->count());
        $this->assertSame(1, Department::where('department_code', 'DEV-HR')->count());
        $this->assertSame(1, Section::where('section_code', 'DEV-HROPS')->count());
        $this->assertSame(1, Position::where('position_code', 'DEV-HRSTAFF')->count());
        $this->assertSame(5, EmploymentStatus::where('status_code', 'like', 'DEV-%')->count());
        $this->assertSame(6, EmployeeClass::where('class_code', 'like', 'DEV-%')->count());
        $this->assertSame(1, User::where('email', 'test@example.com')->count());
    }

    private function employeePayload(array $overrides = []): array
    {
        return array_merge([
            'employee_no' => 'DEV-EMP-001',
            'last_name' => 'Developer',
            'first_name' => 'Sample',
            'gender' => 'Prefer not to say',
            'civil_status' => 'Single',
            'birth_date' => '1990-01-01',
            'company_id' => Company::where('company_code', 'DEV-BOIE')->value('id'),
            'base_id' => Base::where('base_code', 'DEV-MAIN')->value('id'),
            'unit_id' => Unit::where('unit_code', 'DEV-CORP')->value('id'),
            'department_id' => Department::where('department_code', 'DEV-HR')->value('id'),
            'section_id' => Section::where('section_code', 'DEV-HROPS')->value('id'),
            'position_id' => Position::where('position_code', 'DEV-HRSTAFF')->value('id'),
            'employment_status_id' => EmploymentStatus::where('status_code', 'DEV-PROB')->value('id'),
            'employee_class_id' => EmployeeClass::where('class_code', 'DEV-RAF')->value('id'),
            'date_hired' => '2026-08-03',
        ], $overrides);
    }
}
