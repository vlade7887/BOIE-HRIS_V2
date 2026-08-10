<?php

namespace App\Services;

use App\Models\ApprovalDelegation;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ApprovalDelegationService
{
    public function __construct(
        private readonly ApprovalAuditService $audit
    ) {
    }

    public function create(array $data, ?User $actor): ApprovalDelegation
    {
        $this->validateDelegation($data);

        return DB::transaction(function () use ($data, $actor) {
            $delegation = ApprovalDelegation::create($data);

            $this->audit->record(
                $actor,
                'approval_delegation.created',
                $delegation,
                $delegation->only([
                    'acting_for_employee_id',
                    'delegate_employee_id',
                    'effective_from',
                    'effective_until',
                    'reason',
                    'scope_type',
                    'department_id',
                    'status',
                ])
            );

            return $delegation;
        });
    }

    public function update(
        ApprovalDelegation $delegation,
        array $data,
        ?User $actor
    ): ApprovalDelegation {
        if ($delegation->status === ApprovalDelegation::STATUS_REVOKED) {
            throw ValidationException::withMessages([
                'status' => 'A revoked delegation cannot be edited.',
            ]);
        }

        $this->validateDelegation($data, $delegation);

        return DB::transaction(function () use ($delegation, $data, $actor) {
            $before = $delegation->only([
                'acting_for_employee_id',
                'delegate_employee_id',
                'effective_from',
                'effective_until',
                'reason',
                'scope_type',
                'department_id',
                'status',
            ]);

            $delegation->update($data);

            $this->audit->record(
                $actor,
                'approval_delegation.updated',
                $delegation,
                [
                    'before' => $before,
                    'after' => $delegation->only([
                        'acting_for_employee_id',
                        'delegate_employee_id',
                        'effective_from',
                        'effective_until',
                        'reason',
                        'scope_type',
                        'department_id',
                        'status',
                    ]),
                ]
            );

            return $delegation->refresh();
        });
    }

    public function revoke(
        ApprovalDelegation $delegation,
        ?User $actor
    ): ApprovalDelegation {
        if ($delegation->status === ApprovalDelegation::STATUS_REVOKED) {
            throw ValidationException::withMessages([
                'status' => 'This delegation has already been revoked.',
            ]);
        }

        return DB::transaction(function () use ($delegation, $actor) {
            $delegation->update([
                'status' => ApprovalDelegation::STATUS_REVOKED,
                'revoked_at' => now(),
                'revoked_by_user_id' => $actor?->id,
            ]);

            $this->audit->record(
                $actor,
                'approval_delegation.revoked',
                $delegation,
                $delegation->only([
                    'acting_for_employee_id',
                    'delegate_employee_id',
                    'effective_from',
                    'effective_until',
                    'reason',
                    'scope_type',
                    'department_id',
                    'status',
                    'revoked_at',
                    'revoked_by_user_id',
                ])
            );

            return $delegation->refresh();
        });
    }

    public function delete(
        ApprovalDelegation $delegation,
        ?User $actor
    ): void {
        DB::transaction(function () use ($delegation, $actor) {
            $this->audit->record(
                $actor,
                'approval_delegation.archived',
                $delegation,
                $delegation->only([
                    'acting_for_employee_id',
                    'delegate_employee_id',
                    'effective_from',
                    'effective_until',
                    'reason',
                    'status',
                ])
            );

            $delegation->delete();
        });
    }

    private function validateDelegation(
        array $data,
        ?ApprovalDelegation $currentDelegation = null
    ): void {
        $actingForId = (int) $data['acting_for_employee_id'];
        $delegateId = (int) $data['delegate_employee_id'];

        if ($actingForId === $delegateId) {
            throw ValidationException::withMessages([
                'delegate_employee_id' => 'An employee cannot delegate approval authority to themselves.',
            ]);
        }

        $actingFor = Employee::query()->find($actingForId);
        $delegate = Employee::query()->find($delegateId);

        if (! $actingFor || $actingFor->trashed() || ! $actingFor->is_active) {
            throw ValidationException::withMessages([
                'acting_for_employee_id' => 'The acting-for employee must be active.',
            ]);
        }

        if (! $delegate || $delegate->trashed() || ! $delegate->is_active) {
            throw ValidationException::withMessages([
                'delegate_employee_id' => 'The delegate employee must be active and eligible to approve requests.',
            ]);
        }

        if (! $delegate->can_approve_requests) {
            throw ValidationException::withMessages([
                'delegate_employee_id' => 'The delegate employee must be eligible to approve requests.',
            ]);
        }

        if ($data['effective_until'] < $data['effective_from']) {
            throw ValidationException::withMessages([
                'effective_until' => 'The effective until date must be on or after the effective from date.',
            ]);
        }

        $scopeType = $data['scope_type'] ?? ApprovalDelegation::SCOPE_ALL;
        $departmentId = $data['department_id'] ?? null;

        if ($scopeType === ApprovalDelegation::SCOPE_DEPARTMENT && ! $departmentId) {
            throw ValidationException::withMessages([
                'department_id' => 'A department is required for department-scoped delegation.',
            ]);
        }

        if ($scopeType === ApprovalDelegation::SCOPE_ALL) {
            $departmentId = null;
        }

        $overlap = ApprovalDelegation::query()
            ->where('acting_for_employee_id', $actingForId)
            ->where('status', ApprovalDelegation::STATUS_ACTIVE)
            ->when(
                $currentDelegation,
                fn ($query) => $query->whereKeyNot($currentDelegation->id)
            )
            ->where('effective_from', '<=', $data['effective_until'])
            ->where('effective_until', '>=', $data['effective_from'])
            ->where(function ($query) use ($scopeType, $departmentId) {
                if ($scopeType === ApprovalDelegation::SCOPE_ALL) {
                    return;
                }

                $query->where('scope_type', ApprovalDelegation::SCOPE_ALL)
                    ->orWhere(function ($query) use ($departmentId) {
                        $query->where('scope_type', ApprovalDelegation::SCOPE_DEPARTMENT)
                            ->where('department_id', $departmentId);
                    });
            })
            ->exists();

        if ($overlap) {
            throw ValidationException::withMessages([
                'effective_from' => 'This employee already has an overlapping active delegation.',
            ]);
        }

        $reverseDelegationExists = ApprovalDelegation::query()
            ->where('acting_for_employee_id', $delegateId)
            ->where('delegate_employee_id', $actingForId)
            ->where('status', ApprovalDelegation::STATUS_ACTIVE)
            ->when(
                $currentDelegation,
                fn ($query) => $query->whereKeyNot($currentDelegation->id)
            )
            ->where('effective_from', '<=', $data['effective_until'])
            ->where('effective_until', '>=', $data['effective_from'])
            ->where(function ($query) use ($scopeType, $departmentId) {
                if ($scopeType === ApprovalDelegation::SCOPE_ALL) {
                    return;
                }

                $query->where('scope_type', ApprovalDelegation::SCOPE_ALL)
                    ->orWhere(function ($query) use ($departmentId) {
                        $query->where('scope_type', ApprovalDelegation::SCOPE_DEPARTMENT)
                            ->where('department_id', $departmentId);
                    });
            })
            ->exists();

        if ($reverseDelegationExists) {
            throw ValidationException::withMessages([
                'delegate_employee_id' => 'This delegation would create a delegation loop.',
            ]);
        }
    }
}
