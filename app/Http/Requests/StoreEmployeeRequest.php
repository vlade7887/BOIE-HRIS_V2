<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_no' => ['required', 'string', 'max:20', 'unique:employees,employee_no'],
            'biometric_id' => ['nullable', 'string', 'max:30', 'unique:employees,biometric_id'],
            'last_name' => ['required', 'string', 'max:100'],
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'suffix' => ['nullable', 'string', 'max:20'],
            'nickname' => ['nullable', 'string', 'max:100'],
            'gender' => ['required', 'string', 'max:20'],
            'civil_status' => ['required', 'string', 'max:30'],
            'birth_date' => ['required', 'date'],
            'birth_place' => ['nullable', 'string', 'max:150'],
            'nationality' => ['nullable', 'string', 'max:100'],
            'religion' => ['nullable', 'string', 'max:100'],
            'blood_type' => ['nullable', 'string', 'max:10'],
            'profile_photo' => ['nullable'],
            'company_id' => ['required', 'exists:companies,id'],
            'base_id' => ['required', 'exists:bases,id'],
            'unit_id' => ['required', 'exists:units,id'],
            'department_id' => ['required', 'exists:departments,id'],
            'section_id' => ['required', 'exists:sections,id'],
            'position_id' => ['required', 'exists:positions,id'],
            'employment_status_id' => ['required', 'exists:employment_statuses,id'],
            'employee_class_id' => ['required', 'exists:employee_classes,id'],
            'date_hired' => ['required', 'date'],
            'date_regularized' => ['nullable', 'date'],
            'date_resigned' => ['nullable', 'date'],
            'employment_end_date' => ['nullable', 'date'],
            'immediate_supervisor_id' => ['nullable', 'exists:employees,id'],
            'department_head_id' => ['nullable', 'exists:employees,id'],
            'remarks' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'mobile_number' => ['nullable', 'string', 'max:20'],
            'alternate_mobile_number' => ['nullable', 'string', 'max:20'],
            'telephone_number' => ['nullable', 'string', 'max:20'],
            'company_email' => ['nullable', 'email', 'max:150'],
            'personal_email' => ['nullable', 'email', 'max:150'],
            'present_house_number' => ['nullable', 'string', 'max:100'],
            'present_street' => ['nullable', 'string', 'max:150'],
            'present_barangay' => ['nullable', 'string', 'max:150'],
            'present_city' => ['nullable', 'string', 'max:150'],
            'present_province' => ['nullable', 'string', 'max:150'],
            'present_zip_code' => ['nullable', 'string', 'max:20'],
            'permanent_house_number' => ['nullable', 'string', 'max:100'],
            'permanent_street' => ['nullable', 'string', 'max:150'],
            'permanent_barangay' => ['nullable', 'string', 'max:150'],
            'permanent_city' => ['nullable', 'string', 'max:150'],
            'permanent_province' => ['nullable', 'string', 'max:150'],
            'permanent_zip_code' => ['nullable', 'string', 'max:20'],
            'sss_number' => ['nullable', 'string', 'max:50'],
            'philhealth_number' => ['nullable', 'string', 'max:50'],
            'pagibig_number' => ['nullable', 'string', 'max:50'],
            'tin_number' => ['nullable', 'string', 'max:50'],
            'passport_number' => ['nullable', 'string', 'max:50'],
            'driver_license_number' => ['nullable', 'string', 'max:50'],
        ];
    }
}
