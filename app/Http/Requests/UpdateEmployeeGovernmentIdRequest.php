<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeGovernmentIdRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'exists:employees,id'],
            'sss_number' => ['nullable', 'string', 'max:20'],
            'philhealth_number' => ['nullable', 'string', 'max:20'],
            'pagibig_number' => ['nullable', 'string', 'max:20'],
            'tin_number' => ['nullable', 'string', 'max:20'],
        ];
    }
}
