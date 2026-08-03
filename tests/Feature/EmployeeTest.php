<?php

namespace Tests\Feature;

use App\Models\Base;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeAddress;
use App\Models\EmployeeClass;
use App\Models\EmployeeContact;
use App\Models\EmployeeDocument;
use App\Models\EmployeeEmergencyContact;
use App\Models\EmployeeGovernmentId;
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

        $response->assertRedirect(route('employees.show', $employee));
        $response->assertSessionHas('success', 'Employee created successfully.');
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

        $response->assertRedirect(route('employees.show', $employee));
        $response->assertSessionHas('success', 'Employee updated successfully.');
        $this->assertDatabaseHas('employee_contacts', ['employee_id' => $employee->id, 'mobile_number' => '09170000003']);
        $this->assertDatabaseHas('employee_addresses', ['employee_id' => $employee->id, 'present_city' => 'Manila']);
        $this->assertDatabaseHas('employee_government_ids', ['employee_id' => $employee->id, 'passport_number' => 'P7654321']);
    }

    public function test_employee_update_with_blank_nationality_defaults_to_filipino(): void
    {
        $user = User::factory()->create();
        $lookups = $this->createLookups();
        $employee = $this->createEmployee($lookups);

        $this->actingAs($user)->put(route('employees.update', $employee), $this->payload($lookups, ['nationality' => '']));

        $employee->refresh();

        $this->assertSame('Filipino', $employee->nationality);
    }

    public function test_employee_archive_redirects_to_employee_list_with_success_message(): void
    {
        $user = User::factory()->create();
        $employee = $this->createEmployee($this->createLookups());

        $response = $this->actingAs($user)->delete(route('employees.destroy', $employee));

        $response->assertRedirect(route('employees.index'));
        $response->assertSessionHas('success', 'Employee archived successfully.');
        $this->assertSoftDeleted($employee);
    }

    public function test_authenticated_user_can_view_an_employee_profile_with_masked_government_ids(): void
    {
        $user = User::factory()->create();
        $employee = $this->createEmployee($this->createLookups());

        EmployeeContact::create([
            'employee_id' => $employee->id,
            'mobile_number' => '09170000001',
            'alternate_mobile_number' => '09170000002',
            'company_email' => 'juan@boie.test',
        ]);
        EmployeeAddress::create([
            'employee_id' => $employee->id,
            'present_city' => 'Cebu City',
            'permanent_city' => 'Davao City',
        ]);
        EmployeeGovernmentId::create([
            'employee_id' => $employee->id,
            'sss_number' => '12-1234567-8',
            'philhealth_number' => '12-123456789-1',
            'pagibig_number' => '1234-5678-9012',
            'tin_number' => '123-456-789',
            'passport_number' => 'P1234567',
            'driver_license_number' => 'N01-23-456789',
        ]);
        EmployeeEmergencyContact::create([
            'employee_id' => $employee->id,
            'contact_name' => 'Maria Dela Cruz',
            'relationship' => 'Spouse',
            'mobile_number' => '09170000004',
            'address' => 'Cebu City',
        ]);
        EmployeeDocument::create([
            'employee_id' => $employee->id,
            'document_type' => 'Employment Contract',
            'document_name' => 'contract.pdf',
            'file_path' => 'employee-documents/contract.pdf',
            'remarks' => 'Signed contract',
            'uploaded_at' => '2026-07-30 08:00:00',
        ]);

        $response = $this->actingAs($user)->get(route('employees.show', $employee));

        $response->assertOk();
        $response->assertSee(['EMP-001', 'Juan Dela Cruz', 'Cebu City', 'Employment Contract', 'Maria Dela Cruz']);
        $response->assertSee(['Biometric ID', 'Nickname', 'Birth Place', 'Nationality', 'Religion', 'Blood Type', 'Date Regularized', 'Employment End Date', 'Remarks']);
        $response->assertSee(['**-*******-*', '***-***-***', '****4567']);
        $response->assertDontSee([
            '12-1234567-8',
            '12-123456789-1',
            '1234-5678-9012',
            '123-456-789',
            'P1234567',
            'N01-23-456789',
            'employee-documents/contract.pdf',
        ]);
    }

    public function test_employee_profile_displays_the_newly_supported_fields_and_masks_sensitive_values(): void
    {
        $user = User::factory()->create();
        $employee = $this->createEmployee($this->createLookups());

        $employee->update([
            'biometric_id' => 'BIO-001',
            'nickname' => 'Juanjo',
            'birth_place' => 'Cebu City',
            'nationality' => 'Filipino',
            'religion' => 'Roman Catholic',
            'blood_type' => 'O+',
            'date_regularized' => '2021-01-01',
            'employment_end_date' => '2030-01-01',
            'remarks' => 'Senior staff',
        ]);

        $employee->employeeContact()->create([
            'mobile_number' => '09170000001',
            'alternate_mobile_number' => '09170000002',
            'telephone_number' => '1234567',
            'company_email' => 'juan@boie.test',
            'personal_email' => 'juan@example.test',
        ]);

        $response = $this->actingAs($user)->get(route('employees.show', $employee));

        $response->assertOk();
        $response->assertSee('BIO-001');
        $response->assertSee('Juanjo');
        $response->assertSee('Cebu City');
        $response->assertSee('Filipino');
        $response->assertSee('Roman Catholic');
        $response->assertSee('O+');
        $response->assertSee('January 1, 2021');
        $response->assertSee('January 1, 2030');
        $response->assertSee('Senior staff');
        $response->assertSee('09170000001');
        $response->assertSee('09170000002');
        $response->assertSee('1234567');
        $response->assertSee('juan@boie.test');
        $response->assertSee('juan@example.test');
        $response->assertDontSee('P1234567');
        $response->assertDontSee('N01-23-456789');
    }

    public function test_employee_profile_handles_missing_optional_relationships_and_unknown_employees(): void
    {
        $user = User::factory()->create();
        $employee = $this->createEmployee($this->createLookups());

        $this->actingAs($user)
            ->get(route('employees.show', $employee))
            ->assertOk()
            ->assertSee('Not provided')
            ->assertSee('No emergency contacts recorded.')
            ->assertSee('No documents recorded.');

        $this->actingAs($user)
            ->get(route('employees.show', 999999))
            ->assertNotFound();
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

    private function createEmployee(array $lookups): Employee
    {
        return Employee::create(collect($this->payload($lookups))
            ->only((new Employee())->getFillable())
            ->all());
    }
}
