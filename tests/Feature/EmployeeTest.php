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

class EmployeeTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_create_and_update_persist_supporting_records(): void
    {
        $user = User::factory()->create();
        $lookups = $this->createLookups();

        $response = $this->actingAs($user)->post(route('employees.store'), $this->payload($lookups));

        $employee = Employee::where('employee_no', 'EMP-001')->firstOrFail();

        $response->assertRedirect(route('employees.edit', $employee));
        $this->assertDatabaseHas('employee_contacts', [
            'employee_id' => $employee->id,
            'mobile_number' => '09170000001',
            'alternate_mobile_number' => '09170000002',
        ]);
        $this->assertDatabaseHas('employee_addresses', [
            'employee_id' => $employee->id,
            'present_city' => 'Cebu City',
            'permanent_city' => 'Davao City',
        ]);
        $this->assertDatabaseHas('employee_government_ids', [
            'employee_id' => $employee->id,
            'passport_number' => 'P1234567',
            'driver_license_number' => 'N01-23-456789',
        ]);

        $response = $this->actingAs($user)->put(route('employees.update', $employee), $this->payload($lookups, [
            'mobile_number' => '09170000003',
            'present_city' => 'Manila',
            'passport_number' => 'P7654321',
        ]));

        $response->assertRedirect(route('employees.edit', $employee));
        $this->assertDatabaseHas('employee_contacts', ['employee_id' => $employee->id, 'mobile_number' => '09170000003']);
        $this->assertDatabaseHas('employee_addresses', ['employee_id' => $employee->id, 'present_city' => 'Manila']);
        $this->assertDatabaseHas('employee_government_ids', ['employee_id' => $employee->id, 'passport_number' => 'P7654321']);
    }

    private function createLookups(): array
    {
        $company = Company::create(['company_code' => 'BOIE', 'company_name' => 'BOIE']);
        $base = Base::create(['base_code' => 'CEB', 'base_name' => 'Cebu']);
        $unit = Unit::create(['base_id' => $base->id, 'unit_code' => 'UNIT', 'unit_name' => 'Unit']);
        $department = Department::create(['unit_id' => $unit->id, 'department_code' => 'HR', 'department_name' => 'Human Resources']);
        $section = Section::create(['department_id' => $department->id, 'section_code' => 'ADMIN', 'section_name' => 'Administration']);
        $position = Position::create(['section_id' => $section->id, 'position_code' => 'STAFF', 'position_name' => 'HR Staff']);
        $employmentStatus = EmploymentStatus::create(['status_code' => 'REG', 'status_name' => 'Regular']);
        $employeeClass = EmployeeClass::create(['class_code' => 'RANK', 'class_name' => 'Rank and File']);

        return compact('company', 'base', 'unit', 'department', 'section', 'position', 'employmentStatus', 'employeeClass');
    }

    private function payload(array $lookups, array $overrides = []): array
    {
        return array_merge([
            'employee_no' => 'EMP-001', 'last_name' => 'Dela Cruz', 'first_name' => 'Juan', 'gender' => 'Male',
            'civil_status' => 'Single', 'birth_date' => '1990-01-01', 'company_id' => $lookups['company']->id,
            'base_id' => $lookups['base']->id, 'unit_id' => $lookups['unit']->id, 'department_id' => $lookups['department']->id,
            'section_id' => $lookups['section']->id, 'position_id' => $lookups['position']->id,
            'employment_status_id' => $lookups['employmentStatus']->id, 'employee_class_id' => $lookups['employeeClass']->id,
            'date_hired' => '2020-01-01', 'mobile_number' => '09170000001', 'alternate_mobile_number' => '09170000002',
            'telephone_number' => '1234567', 'company_email' => 'juan@boie.test', 'personal_email' => 'juan@example.test',
            'present_house_number' => '1', 'present_street' => 'Main', 'present_barangay' => 'Barangay 1',
            'present_city' => 'Cebu City', 'present_province' => 'Cebu', 'present_zip_code' => '6000',
            'permanent_house_number' => '2', 'permanent_street' => 'Second', 'permanent_barangay' => 'Barangay 2',
            'permanent_city' => 'Davao City', 'permanent_province' => 'Davao del Sur', 'permanent_zip_code' => '8000',
            'sss_number' => 'SSS-001', 'philhealth_number' => 'PH-001', 'pagibig_number' => 'PAG-001', 'tin_number' => 'TIN-001',
            'passport_number' => 'P1234567', 'driver_license_number' => 'N01-23-456789',
        ], $overrides);
    }
}
