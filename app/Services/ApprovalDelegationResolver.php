<?php

namespace App\Services;

use App\Models\ApprovalDelegation;
use App\Models\ApprovalRequest;
use App\Models\Employee;
use Illuminate\Validation\ValidationException;

class ApprovalDelegationResolver
{
    public function resolve(
        ApprovalRequest $request,
        Employee $canonicalApprover,
        Employee $actor
    ): ?ApprovalDelegation {
        $today = now(config('app.timezone'))->toDateString();

        $baseQuery = fn () => ApprovalDelegation::query()
            ->where('acting_for_employee_id', $canonicalApprover->id)
            ->where('delegate_employee_id', $actor->id)
            ->where('status', ApprovalDelegation::STATUS_ACTIVE)
            ->whereNull('revoked_at')
            ->whereDate('effective_from', '<=', $today)
            ->whereDate('effective_until', '>=', $today)
            ->lockForUpdate();

        if ($request->request_department_id !== null) {
            $departmentMatches = $baseQuery()
                ->where('scope_type', ApprovalDelegation::SCOPE_DEPARTMENT)
                ->where('department_id', $request->request_department_id)
                ->get();

            if ($departmentMatches->count() > 1) {
                throw ValidationException::withMessages([
                    'approver' => 'Multiple department-scoped delegations match this approval.',
                ]);
            }

            if ($departmentMatches->isNotEmpty()) {
                return $this->requireActiveDelegate($departmentMatches->first(), $actor);
            }
        }

        $allMatches = $baseQuery()
            ->where('scope_type', ApprovalDelegation::SCOPE_ALL)
            ->get();

        if ($allMatches->count() > 1) {
            throw ValidationException::withMessages([
                'approver' => 'Multiple All Approvals delegations match this approval.',
            ]);
        }

        return $allMatches->isNotEmpty()
            ? $this->requireActiveDelegate($allMatches->first(), $actor)
            : null;
    }

    private function requireActiveDelegate(
        ApprovalDelegation $delegation,
        Employee $actor
    ): ApprovalDelegation {
        if ($actor->trashed() || ! $actor->is_active) {
            throw ValidationException::withMessages([
                'approver' => 'The delegated approver is no longer active.',
            ]);
        }

        return $delegation;
    }
}
