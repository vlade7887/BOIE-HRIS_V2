<?php

namespace App\Http\Requests;

use App\Models\ApprovalDelegation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreApprovalDelegationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'acting_for_employee_id' => [
                'required',
                Rule::exists('employees', 'id')
                    ->whereNull('deleted_at')
                    ->where('is_active', true),
            ],

            'delegate_employee_id' => [
                'required',
                'different:acting_for_employee_id',
                Rule::exists('employees', 'id')
                    ->whereNull('deleted_at')
                    ->where('is_active', true)
                    ->where('can_approve_requests', true),
            ],

            'effective_from' => [
                'required',
                'date',
            ],

            'effective_until' => [
                'required',
                'date',
                'after_or_equal:effective_from',
            ],

            'reason' => [
                'required',
                'string',
            ],

            'scope_type' => ['required', Rule::in(ApprovalDelegation::scopeTypes())],

            'department_id' => [
                'nullable',
                'required_if:scope_type,department',
                Rule::exists('departments', 'id')->whereNull('deleted_at')->where('is_active', true),
            ],

            'status' => [
                'required',
                Rule::in(['active']),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'status' => 'active',
            'scope_type' => $this->input('scope_type', ApprovalDelegation::SCOPE_ALL),
            'department_id' => $this->input('scope_type', ApprovalDelegation::SCOPE_ALL) === ApprovalDelegation::SCOPE_ALL
                ? null
                : $this->input('department_id'),
        ]);
    }
}
