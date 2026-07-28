<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmploymentStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $employmentStatusId = $this->route('employment_status')?->id ?? $this->route('employment_status');

        return [
            'status_code' => ['required', 'string', 'max:20', Rule::unique('employment_statuses', 'status_code')->ignore($employmentStatusId)],
            'status_name' => ['required', 'string', 'max:150'],
            'remarks' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
