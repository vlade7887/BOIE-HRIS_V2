<?php

namespace App\Services;

use App\Contracts\Approvable;
use App\Models\ApprovalRequest;
use App\Models\ApprovalRequestAction;
use App\Models\ApprovalRequestStep;
use App\Models\ApprovalWorkflow;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ApprovalRequestSubmissionService
{
    public function __construct(
        private readonly ApprovalAuditService $audit
    ) {
    }

    public function submit(
        Approvable $approvable,
        array $selectedApproverEmployeeIds,
        ?User $actor,
        ?string $idempotencyKey = null
    ): ApprovalRequest {
        if (! $actor) {
            throw ValidationException::withMessages([
                'actor' => 'An authenticated requester is required.',
            ]);
        }

        return DB::transaction(function () use (
            $approvable,
            $selectedApproverEmployeeIds,
            $actor,
            $idempotencyKey
        ) {
            $requester = $approvable->approvalRequester();
            $mappedEmployee = Employee::withTrashed()
                ->where('user_id', $actor->id)
                ->first();

            if (
                ! $mappedEmployee ||
                $mappedEmployee->trashed() ||
                ! $mappedEmployee->is_active ||
                $mappedEmployee->id !== $requester->id
            ) {
                throw ValidationException::withMessages([
                    'requester' => 'The authenticated user must map to the request employee.',
                ]);
            }

            $request = ApprovalRequest::query()
                ->where('approvable_type', $approvable->approvalType())
                ->where('approvable_id', $approvable->approvalId())
                ->lockForUpdate()
                ->first();

            if ($request) {
                $existing = $this->existingIdempotentAction(
                    $request,
                    ApprovalRequestAction::ACTION_SUBMIT,
                    $idempotencyKey
                );

                if ($existing) {
                    return $request->refresh();
                }

                if ($request->status !== ApprovalRequest::STATUS_DRAFT) {
                    throw ValidationException::withMessages([
                        'request' => 'This request has already been submitted.',
                    ]);
                }
            } else {
                $request = ApprovalRequest::create([
                    'requester_employee_id' => $requester->id,
                    'module_key' => $approvable->approvalModuleKey(),
                    'approvable_type' => $approvable->approvalType(),
                    'approvable_id' => $approvable->approvalId(),
                    'status' => ApprovalRequest::STATUS_DRAFT,
                ]);
            }

            $workflows = ApprovalWorkflow::query()
                ->where('module_key', $approvable->approvalModuleKey())
                ->where('status', ApprovalWorkflow::STATUS_ACTIVE)
                ->lockForUpdate()
                ->get();

            if ($workflows->count() !== 1) {
                throw ValidationException::withMessages([
                    'workflow' => $workflows->isEmpty()
                        ? 'No active approval workflow exists for this module.'
                        : 'Multiple active approval workflows exist for this module.',
                ]);
            }

            $workflow = $workflows->first();
            $selectedIds = array_map('intval', array_values($selectedApproverEmployeeIds));

            if (count($selectedIds) !== count(array_unique($selectedIds))) {
                throw ValidationException::withMessages([
                    'approvers' => 'Approvers must be unique.',
                ]);
            }

            if (
                count($selectedIds) < $workflow->min_approvers ||
                count($selectedIds) > $workflow->max_approvers
            ) {
                throw ValidationException::withMessages([
                    'approvers' => 'The selected approver count is outside the workflow limits.',
                ]);
            }

            if (in_array($requester->id, $selectedIds, true)) {
                throw ValidationException::withMessages([
                    'approvers' => 'The requester cannot approve their own request.',
                ]);
            }

            $approvers = Employee::query()
                ->whereIn('id', $selectedIds)
                ->whereNull('deleted_at')
                ->where('is_active', true)
                ->where('can_approve_requests', true)
                ->get()
                ->keyBy('id');

            if ($approvers->count() !== count($selectedIds)) {
                throw ValidationException::withMessages([
                    'approvers' => 'Every approver must be active and eligible.',
                ]);
            }

            $hrApproverId = $workflow->hr_final_required
                ? $workflow->hr_final_approver_employee_id
                : null;

            if ($workflow->hr_final_required && ! $hrApproverId) {
                throw ValidationException::withMessages([
                    'workflow' => 'The active workflow has no HR final approver.',
                ]);
            }

            if ($hrApproverId !== null && $hrApproverId === $requester->id) {
                throw ValidationException::withMessages([
                    'workflow' => 'The requester cannot be the HR final approver.',
                ]);
            }

            if ($hrApproverId !== null && in_array($hrApproverId, $selectedIds, true)) {
                throw ValidationException::withMessages([
                    'approvers' => 'The HR final approver cannot be selected as a normal approver.',
                ]);
            }

            if ($hrApproverId !== null) {
                $hrApprover = Employee::query()->find($hrApproverId);

                if (
                    ! $hrApprover ||
                    $hrApprover->trashed() ||
                    ! $hrApprover->is_active ||
                    ! $hrApprover->can_approve_requests
                ) {
                    throw ValidationException::withMessages([
                        'workflow' => 'The HR final approver is no longer eligible.',
                    ]);
                }
            }

            $request->update([
                'request_department_id' => $approvable->approvalDepartmentId(),
                'approval_workflow_id' => $workflow->id,
                'workflow_code' => $workflow->code,
                'workflow_version' => $workflow->version,
                'workflow_name' => $workflow->name,
                'status' => ApprovalRequest::STATUS_PENDING,
                'current_step_order' => 1,
                'submitted_at' => now(),
            ]);

            foreach ($selectedIds as $index => $employeeId) {
                ApprovalRequestStep::create([
                    'approval_request_id' => $request->id,
                    'step_order' => $index + 1,
                    'canonical_approver_employee_id' => $employeeId,
                    'step_type' => ApprovalRequestStep::TYPE_SELECTED,
                    'status' => $index === 0
                        ? ApprovalRequestStep::STATUS_PENDING
                        : ApprovalRequestStep::STATUS_WAITING,
                    'activated_at' => $index === 0 ? now() : null,
                ]);
            }

            if ($hrApproverId !== null) {
                ApprovalRequestStep::create([
                    'approval_request_id' => $request->id,
                    'step_order' => count($selectedIds) + 1,
                    'canonical_approver_employee_id' => $hrApproverId,
                    'step_type' => ApprovalRequestStep::TYPE_HR_FINAL,
                    'status' => ApprovalRequestStep::STATUS_WAITING,
                ]);
            }

            $action = $this->createAction(
                $request,
                ApprovalRequestAction::ACTION_SUBMIT,
                $actor,
                null,
                null,
                null,
                null,
                $idempotencyKey
            );

            $this->audit->record(
                $actor,
                'approval_request.submitted',
                $request,
                [
                    'action_id' => $action->id,
                    'workflow_code' => $request->workflow_code,
                    'workflow_version' => $request->workflow_version,
                    'request_department_id' => $request->request_department_id,
                ]
            );

            return $request->refresh();
        });
    }

    private function createAction(
        ApprovalRequest $request,
        string $action,
        User $actor,
        ?ApprovalRequestStep $step,
        ?Employee $canonicalApprover,
        ?Employee $actingFor,
        $delegation,
        ?string $idempotencyKey
    ): ApprovalRequestAction {
        return ApprovalRequestAction::create([
            'approval_request_id' => $request->id,
            'approval_request_step_id' => $step?->id,
            'action' => $action,
            'actor_user_id' => $actor->id,
            'actor_employee_id' => Employee::query()->where('user_id', $actor->id)->value('id'),
            'canonical_approver_employee_id' => $canonicalApprover?->id,
            'acting_for_employee_id' => $actingFor?->id,
            'approval_delegation_id' => $delegation?->id,
            'acted_at' => now(),
            'ip_address' => app()->bound('request') ? request()->ip() : null,
            'user_agent' => app()->bound('request') ? request()->userAgent() : null,
            'idempotency_key' => $idempotencyKey,
        ]);
    }

    private function existingIdempotentAction(
        ApprovalRequest $request,
        string $action,
        ?string $idempotencyKey
    ): ?ApprovalRequestAction {
        if ($idempotencyKey === null) {
            return null;
        }

        $existing = ApprovalRequestAction::query()
            ->where('idempotency_key', $idempotencyKey)
            ->lockForUpdate()
            ->first();

        if (! $existing) {
            return null;
        }

        if ($existing->approval_request_id !== $request->id || $existing->action !== $action) {
            throw ValidationException::withMessages([
                'idempotency_key' => 'This idempotency key was already used for another action.',
            ]);
        }

        return $existing;
    }
}
