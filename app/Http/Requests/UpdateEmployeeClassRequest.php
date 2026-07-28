<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $employeeClassId = $this->route('employee_class')?->id ?? $this->route('employee_class');

        return [
            'class_code' => ['required', 'string', 'max:20', Rule::unique('employee_classes', 'class_code')->ignore($employeeClassId)],
            'class_name' => ['required', 'string', 'max:150'],
            'remarks' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
