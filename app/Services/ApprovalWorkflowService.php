<?php

namespace App\Services;

use App\Models\ApprovalWorkflow;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ApprovalWorkflowService
{
    public function __construct(
        private readonly ApprovalAuditService $audit
    ) {
    }

    public function create(array $data, ?User $actor): ApprovalWorkflow
    {
        $this->validateTemplate($data);

        return DB::transaction(function () use ($data, $actor) {
            $workflow = ApprovalWorkflow::create($data);

            $this->audit->record(
                $actor,
                'approval_workflow.created',
                $workflow,
                $workflow->only([
                    'code',
                    'version',
                    'name',
                    'description',
                    'module_key',
                    'min_approvers',
                    'max_approvers',
                    'hr_final_required',
                    'hr_final_approver_employee_id',
                    'status',
                ])
            );

            return $workflow;
        });
    }

    public function update(
        ApprovalWorkflow $workflow,
        array $data,
        ?User $actor
    ): ApprovalWorkflow {
        if ($workflow->status === 'active') {
            throw ValidationException::withMessages([
                'workflow' => 'Active workflows cannot be edited directly. Create a new workflow version instead.',
            ]);
        }

        $this->validateTemplate($data);

        return DB::transaction(function () use ($workflow, $data, $actor) {
            $before = $workflow->only([
                'code',
                'version',
                'name',
                'description',
                'module_key',
                'min_approvers',
                'max_approvers',
                'hr_final_required',
                'hr_final_approver_employee_id',
                'status',
            ]);

            $workflow->update($data);

            $this->audit->record(
                $actor,
                'approval_workflow.updated',
                $workflow,
                [
                    'before' => $before,
                    'after' => $workflow->only([
                        'code',
                        'version',
                        'name',
                        'description',
                        'module_key',
                        'min_approvers',
                        'max_approvers',
                        'hr_final_required',
                        'hr_final_approver_employee_id',
                        'status',
                    ]),
                ]
            );

            return $workflow->refresh();
        });
    }

    public function delete(
        ApprovalWorkflow $workflow,
        ?User $actor
    ): void {
        DB::transaction(function () use ($workflow, $actor) {
            $workflow->update([
                'status' => 'archived',
            ]);

            $this->audit->record(
                $actor,
                'approval_workflow.archived',
                $workflow,
                $workflow->only([
                    'code',
                    'version',
                    'name',
                    'module_key',
                    'min_approvers',
                    'max_approvers',
                    'hr_final_required',
                    'hr_final_approver_employee_id',
                    'status',
                ])
            );

            $workflow->delete();
        });
    }

    private function validateTemplate(array $data): void
    {
        $min = (int) ($data['min_approvers'] ?? 1);
        $max = (int) ($data['max_approvers'] ?? 5);

        if ($min < 1 || $max < $min || $max > 20) {
            throw ValidationException::withMessages([
                'max_approvers' => 'The maximum approvers must be at least the minimum and no more than 20.',
            ]);
        }

        $approverId = $data['hr_final_approver_employee_id'] ?? null;
        if ($approverId !== null) {
            $approver = Employee::query()->find($approverId);
            if (! $approver || $approver->trashed() || ! $approver->is_active || ! $approver->can_approve_requests) {
                throw ValidationException::withMessages([
                    'hr_final_approver_employee_id' => 'The HR final approver must be an active eligible employee.',
                ]);
            }
        }

        if (($data['status'] ?? ApprovalWorkflow::STATUS_DRAFT) === ApprovalWorkflow::STATUS_ACTIVE) {
            if (empty($data['module_key'])) {
                throw ValidationException::withMessages([
                    'module_key' => 'An active workflow template must have a module key.',
                ]);
            }

            if (($data['hr_final_required'] ?? true) && ! $approverId) {
                throw ValidationException::withMessages([
                    'hr_final_approver_employee_id' => 'An active workflow template must have an eligible HR final approver.',
                ]);
            }
        }
    }
}
