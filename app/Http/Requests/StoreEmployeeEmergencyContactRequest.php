<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeEmergencyContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'exists:employees,id'],
            'contact_name' => ['required', 'string', 'max:150'],
            'relationship' => ['required', 'string', 'max:100'],
            'mobile_number' => ['nullable', 'string', 'max:20'],
            'telephone_number' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
        ];
    }
}
