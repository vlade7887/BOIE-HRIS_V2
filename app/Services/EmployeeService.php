<?php

namespace App\Services;

use App\Models\Employee;
use Illuminate\Support\Facades\DB;

class EmployeeService
{
    public function create(array $data): Employee
    {
        return DB::transaction(function () use ($data) {
            $employee = Employee::create($this->employeeData($data));

            $employee->employeeContact()->create($this->contactData($data));
            $employee->employeeAddress()->create($this->addressData($data));
            $employee->employeeGovernmentId()->create($this->governmentIdData($data));

            return $employee;
        });
    }

    public function update(Employee $employee, array $data): Employee
    {
        return DB::transaction(function () use ($employee, $data) {
            $employee->update($this->employeeData($data));

            $employee->employeeContact()->updateOrCreate([], $this->contactData($data));
            $employee->employeeAddress()->updateOrCreate([], $this->addressData($data));
            $employee->employeeGovernmentId()->updateOrCreate([], $this->governmentIdData($data));

            return $employee;
        });
    }

    private function employeeData(array $data): array
    {
        $employeeData = collect($data)->only((new Employee())->getFillable())->all();

        $employeeData['nationality'] = $this->normalizeNationality($employeeData['nationality'] ?? null);

        return $employeeData;
    }

    private function normalizeNationality(?string $nationality): string
    {
        $normalized = trim((string) $nationality);

        return $normalized !== '' ? $normalized : 'Filipino';
    }

    private function contactData(array $data): array
    {
        return collect($data)->only([
            'mobile_number', 'alternate_mobile_number', 'telephone_number', 'company_email', 'personal_email',
        ])->all();
    }

    private function addressData(array $data): array
    {
        return collect($data)->only([
            'present_house_number', 'present_street', 'present_barangay', 'present_city', 'present_province', 'present_zip_code',
            'permanent_house_number', 'permanent_street', 'permanent_barangay', 'permanent_city', 'permanent_province', 'permanent_zip_code',
        ])->all();
    }

    private function governmentIdData(array $data): array
    {
        return collect($data)->only([
            'sss_number', 'philhealth_number', 'pagibig_number', 'tin_number', 'passport_number', 'driver_license_number',
        ])->all();
    }
}
