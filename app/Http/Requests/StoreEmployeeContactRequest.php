<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'exists:employees,id'],
            'mobile_number' => ['nullable', 'string', 'max:20'],
            'telephone_number' => ['nullable', 'string', 'max:20'],
            'company_email' => ['nullable', 'email', 'max:150'],
            'personal_email' => ['nullable', 'email', 'max:150'],
        ];
    }
}
