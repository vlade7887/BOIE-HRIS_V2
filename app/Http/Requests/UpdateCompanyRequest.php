<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $companyId = $this->route('company')?->id ?? $this->route('company');

        return [
            'company_code' => ['required', 'string', 'max:20', Rule::unique('companies', 'company_code')->ignore($companyId)],
            'company_name' => ['required', 'string', 'max:150'],
            'contact_person' => ['nullable', 'string', 'max:150'],
            'contact_number' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'address' => ['nullable', 'string'],
            'remarks' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
