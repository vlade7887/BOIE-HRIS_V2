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
            'filing_timing' => ['sometimes', 'required', 'string', 'in:advance,after_return,same_day'],
            'minimum_advance_days' => ['sometimes', 'required', 'integer', 'min:0'],
            'carryover_policy' => ['sometimes', 'required', 'string', 'in:grace_period,payout,expire'],
            'carryover_grace_days' => ['sometimes', 'required', 'integer', 'min:0'],
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

        if ($this->has('filing_timing')) {
            $this->merge([
                'filing_timing' => strtolower(trim((string) $this->input('filing_timing'))),
                'minimum_advance_days' => (int) $this->input('minimum_advance_days', 0),
                'carryover_policy' => strtolower(trim((string) $this->input('carryover_policy', 'expire'))),
                'carryover_grace_days' => (int) $this->input('carryover_grace_days', 0),
            ]);
        }
    }
}
