<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeUserMappingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $employee = $this->route('employee');
        $employeeId = is_object($employee)
            ? $employee->id
            : $employee;

        return [
            'user_id' => [
                'nullable',
                Rule::exists('users', 'id'),
                Rule::unique('employees', 'user_id')
                    ->ignore($employeeId),
            ],
        ];
    }
}
