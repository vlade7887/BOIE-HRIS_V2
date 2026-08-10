<?php

namespace App\Http\Requests;

use App\Models\ApprovalWorkflow;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreApprovalWorkflowRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('approval_workflows')
                    ->where(fn ($query) => $query->where(
                        'version',
                        $this->integer('version', 1)
                    )),
            ],

            'version' => [
                'required',
                'integer',
                'min:1',
                'max:65535',
            ],

            'name' => [
                'required',
                'string',
                'max:150',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'module_key' => ['required', Rule::in(ApprovalWorkflow::moduleKeys())],

            'min_approvers' => ['required', 'integer', 'min:1', 'max:20'],

            'max_approvers' => ['required', 'integer', 'min:1', 'max:20', 'gte:min_approvers'],

            'hr_final_required' => ['required', 'boolean'],

            'hr_final_approver_employee_id' => [
                'nullable',
                Rule::exists('employees', 'id')
                    ->whereNull('deleted_at')
                    ->where('is_active', true)
                    ->where('can_approve_requests', true),
            ],

            'status' => [
                'required',
                Rule::in([
                    ...ApprovalWorkflow::statuses(),
                ]),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => $this->filled('code')
                ? strtoupper(trim((string) $this->input('code')))
                : null,

            'version' => $this->input('version', 1),

            'status' => $this->input('status', 'draft'),
            'min_approvers' => $this->input('min_approvers', 1),
            'max_approvers' => $this->input('max_approvers', 5),
            'hr_final_required' => $this->boolean('hr_final_required', true),
        ]);
    }
}
