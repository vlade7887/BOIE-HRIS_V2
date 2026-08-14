<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLeaveTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $leaveTypeId = $this->route('leave_type')?->id ?? $this->route('leave_type');

        return [
            'code' => ['required', 'string', 'max:20', Rule::unique('leave_types', 'code')->ignore($leaveTypeId)],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'annual_entitlement_days' => ['required', 'numeric', 'min:0', 'multiple_of:0.5'],
            'allows_half_day' => ['nullable', 'boolean'],
            'requires_attachment' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => $this->filled('code') ? strtoupper(trim((string) $this->input('code'))) : null,
            'allows_half_day' => $this->boolean('allows_half_day'),
            'requires_attachment' => $this->boolean('requires_attachment'),
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}
