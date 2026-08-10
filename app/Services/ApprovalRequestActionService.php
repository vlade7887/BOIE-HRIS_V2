<?php

namespace App\Services;

use App\Models\ApprovalRequest;
use App\Models\ApprovalRequestAction;
use App\Models\ApprovalRequestStep;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ApprovalRequestActionService
{
    public function __construct(
        private readonly ApprovalAuditService $audit,
        private readonly ApprovalDelegationResolver $delegations
    ) {
    }

    public function approve(
        ApprovalRequest $request,
        ?User $actor,
        ?string $idempotencyKey = null,
        ?string $remarks = null
    ): ApprovalRequest {
        return $this->decide(
            $request,
            $actor,
            ApprovalRequestAction::ACTION_APPROVE,
            $idempotencyKey,
            $remarks
        );
    }

    public function reject(
        ApprovalRequest $request,
        ?User $actor,
        ?string $idempotencyKey = null,
        ?string $remarks = null
    ): ApprovalRequest {
        return $this->decide(
            $request,
            $actor,
            ApprovalRequestAction::ACTION_REJECT,
            $idempotencyKey,
            $remarks
        );
    }

    public function cancel(
        ApprovalRequest $request,
        ?User $actor,
        ?string $idempotencyKey = null,
        ?string $remarks = null
    ): ApprovalRequest {
        if (! $actor) {
            throw ValidationException::withMessages([
                'actor' => 'An authenticated requester is required.',
            ]);
        }

        return DB::transaction(function () use ($request, $actor, $idempotencyKey, $remarks) {
            $lockedRequest = ApprovalRequest::query()->lockForUpdate()->findOrFail($request->id);
            $existing = $this->existingIdempotentAction(
                $lockedRequest,
                ApprovalRequestAction::ACTION_CANCEL,
                $idempotencyKey
            );

            if ($existing) {
                return $lockedRequest->refresh();
            }

            if (! in_array($lockedRequest->status, [
                ApprovalRequest::STATUS_DRAFT,
                ApprovalRequest::STATUS_PENDING,
            ], true)) {
                throw ValidationException::withMessages([
                    'request' => 'A terminal request cannot be cancelled.',
                ]);
            }

            $requester = $lockedRequest->requester;
            $requesterEmployee = Employee::withTrashed()
                ->where('user_id', $actor->id)
                ->first();

            if (
                ! $requesterEmployee ||
                $requesterEmployee->trashed() ||
                ! $requesterEmployee->is_active ||
                $requesterEmployee->id !== $requester->id
            ) {
                throw ValidationException::withMessages([
                    'actor' => 'Only the authenticated requester may cancel this request.',
                ]);
            }

            $now = now();
            if ($lockedRequest->status === ApprovalRequest::STATUS_PENDING) {
                $lockedRequest->steps()
                    ->whereIn('status', [
                        ApprovalRequestStep::STATUS_PENDING,
                        ApprovalRequestStep::STATUS_WAITING,
                    ])
                    ->update([
                        'status' => ApprovalRequestStep::STATUS_CANCELLED,
                        'cancelled_at' => $now,
                        'updated_at' => $now,
                    ]);
            }

            $lockedRequest->update([
                'status' => ApprovalRequest::STATUS_CANCELLED,
                'current_step_order' => null,
                'cancelled_at' => $now,
                'completed_at' => $now,
            ]);

            $action = $this->createAction(
                $lockedRequest,
                ApprovalRequestAction::ACTION_CANCEL,
                $actor,
                null,
                null,
                null,
                null,
                $remarks,
                $idempotencyKey
            );

            $this->audit->record(
                $actor,
                'approval_request.cancelled',
                $lockedRequest,
                ['action_id' => $action->id, 'remarks' => $remarks]
            );

            return $lockedRequest->refresh();
        });
    }

    private function decide(
        ApprovalRequest $request,
        ?User $actor,
        string $decision,
        ?string $idempotencyKey,
        ?string $remarks
    ): ApprovalRequest {
        if (! $actor) {
            throw ValidationException::withMessages([
                'actor' => 'An authenticated approver is required.',
            ]);
        }

        return DB::transaction(function () use (
            $request,
            $actor,
            $decision,
            $idempotencyKey,
            $remarks
        ) {
            $lockedRequest = ApprovalRequest::query()->lockForUpdate()->findOrFail($request->id);
            $existing = $this->existingIdempotentAction($lockedRequest, $decision, $idempotencyKey);

            if ($existing) {
                return $lockedRequest->refresh();
            }

            if ($lockedRequest->status !== ApprovalRequest::STATUS_PENDING) {
                throw ValidationException::withMessages([
                    'request' => 'Only pending requests may receive approval actions.',
                ]);
            }

            if ($lockedRequest->current_step_order === null) {
                throw ValidationException::withMessages([
                    'request' => 'The pending request has no current approval step.',
                ]);
            }

            $step = ApprovalRequestStep::query()
                ->where('approval_request_id', $lockedRequest->id)
                ->where('step_order', $lockedRequest->current_step_order)
                ->lockForUpdate()
                ->first();

            if (! $step || $step->status !== ApprovalRequestStep::STATUS_PENDING) {
                throw ValidationException::withMessages([
                    'step' => 'Only the current pending approval step may be acted upon.',
                ]);
            }

            [$actorEmployee, $delegation] = $this->authorizeActor(
                $lockedRequest,
                $step,
                $actor
            );

            $now = now();
            $action = $this->createAction(
                $lockedRequest,
                $decision,
                $actor,
                $step,
                $step->canonicalApprover,
                $delegation ? $step->canonicalApprover : null,
                $delegation,
                $remarks,
                $idempotencyKey
            );

            if ($decision === ApprovalRequestAction::ACTION_APPROVE) {
                $step->update([
                    'status' => ApprovalRequestStep::STATUS_APPROVED,
                    'acted_at' => $now,
                ]);

                $next = ApprovalRequestStep::query()
                    ->where('approval_request_id', $lockedRequest->id)
                    ->where('step_order', $step->step_order + 1)
                    ->lockForUpdate()
                    ->first();

                if ($next) {
                    if ($next->status !== ApprovalRequestStep::STATUS_WAITING) {
                        throw ValidationException::withMessages([
                            'step' => 'The next approval step is not waiting.',
                        ]);
                    }

                    $next->update([
                        'status' => ApprovalRequestStep::STATUS_PENDING,
                        'activated_at' => $now,
                    ]);
                    $lockedRequest->update([
                        'current_step_order' => $next->step_order,
                    ]);
                } else {
                    $lockedRequest->update([
                        'status' => ApprovalRequest::STATUS_APPROVED,
                        'current_step_order' => null,
                        'completed_at' => $now,
                    ]);
                }
            } else {
                $step->update([
                    'status' => ApprovalRequestStep::STATUS_REJECTED,
                    'acted_at' => $now,
                ]);
                $lockedRequest->steps()
                    ->where('status', ApprovalRequestStep::STATUS_WAITING)
                    ->update([
                        'status' => ApprovalRequestStep::STATUS_CANCELLED,
                        'cancelled_at' => $now,
                        'updated_at' => $now,
                    ]);
                $lockedRequest->update([
                    'status' => ApprovalRequest::STATUS_REJECTED,
                    'current_step_order' => null,
                    'completed_at' => $now,
                ]);
            }

            $this->audit->record(
                $actor,
                'approval_request.' . ($decision === ApprovalRequestAction::ACTION_APPROVE ? 'approved' : 'rejected'),
                $lockedRequest,
                [
                    'action_id' => $action->id,
                    'step_id' => $step->id,
                    'canonical_approver_employee_id' => $step->canonical_approver_employee_id,
                    'actor_employee_id' => $actorEmployee->id,
                    'approval_delegation_id' => $delegation?->id,
                ]
            );

            return $lockedRequest->refresh();
        });
    }

    private function authorizeActor(
        ApprovalRequest $request,
        ApprovalRequestStep $step,
        User $actor
    ): array {
        $actorEmployee = Employee::withTrashed()
            ->where('user_id', $actor->id)
            ->first();

        if (! $actorEmployee || $actorEmployee->trashed() || ! $actorEmployee->is_active) {
            throw ValidationException::withMessages([
                'actor' => 'The authenticated user must map to an active employee.',
            ]);
        }

        if ($actorEmployee->id === $request->requester_employee_id) {
            throw ValidationException::withMessages([
                'actor' => 'The requester cannot approve their own request.',
            ]);
        }

        if ($actorEmployee->id === $step->canonical_approver_employee_id) {
            return [$actorEmployee, null];
        }

        $delegation = $this->delegations->resolve(
            $request,
            $step->canonicalApprover,
            $actorEmployee
        );

        if (! $delegation) {
            throw ValidationException::withMessages([
                'actor' => 'The authenticated employee is not authorized for this approval step.',
            ]);
        }

        return [$actorEmployee, $delegation];
    }

    private function createAction(
        ApprovalRequest $request,
        string $action,
        User $actor,
        ?ApprovalRequestStep $step,
        ?Employee $canonicalApprover,
        ?Employee $actingFor,
        $delegation,
        ?string $remarks,
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
            'remarks' => $remarks,
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
