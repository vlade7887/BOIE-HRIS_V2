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
        ];
    }
}
