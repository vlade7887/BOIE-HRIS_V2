<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $departmentId = $this->route('department')?->id ?? $this->route('department');

        return [
            'unit_id' => ['nullable', 'exists:units,id'],
            'department_code' => ['required', 'string', 'max:20', Rule::unique('departments', 'department_code')->ignore($departmentId)],
            'department_name' => ['required', 'string', 'max:150'],
            'remarks' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
