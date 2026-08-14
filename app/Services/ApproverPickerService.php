<?php

namespace App\Services;

use App\Models\ApprovalWorkflow;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class ApproverPickerService
{
    public function requester(User $user): Employee
    {
        $employee = Employee::withTrashed()->where('user_id', $user->id)->first();

        if (! $employee || $employee->trashed() || ! $employee->is_active) {
            throw ValidationException::withMessages([
                'requester' => 'Your account must be mapped to an active employee before filing a request.',
            ]);
        }

        return $employee->loadMissing(['department', 'position', 'immediateSupervisor', 'departmentHead']);
    }

    public function workflow(string $moduleKey): ApprovalWorkflow
    {
        $workflows = ApprovalWorkflow::query()
            ->where('module_key', $moduleKey)
            ->where('status', ApprovalWorkflow::STATUS_ACTIVE)
            ->with('hrFinalApprover')
            ->get();

        if ($workflows->count() !== 1) {
            throw ValidationException::withMessages([
                'workflow' => $workflows->isEmpty()
                    ? 'No active approval workflow exists for this module.'
                    : 'Multiple active approval workflows exist for this module.',
            ]);
        }

        $workflow = $workflows->first();

        if (
            $workflow->hr_final_required &&
            (! $workflow->hrFinalApprover ||
                $workflow->hrFinalApprover->trashed() ||
                ! $workflow->hrFinalApprover->is_active ||
                ! $workflow->hrFinalApprover->can_approve_requests)
        ) {
            throw ValidationException::withMessages([
                'workflow' => 'The active workflow has no available eligible HR final approver.',
            ]);
        }

        return $workflow;
    }

    public function eligibleQuery(Employee $requester, ?ApprovalWorkflow $workflow = null): Builder
    {
        $query = Employee::query()
            ->with(['department', 'position'])
            ->whereNull('employees.deleted_at')
            ->where('employees.is_active', true)
            ->where('employees.can_approve_requests', true)
            ->whereKeyNot($requester->id);

        if ($workflow?->hr_final_required && $workflow->hr_final_approver_employee_id) {
            $query->whereKeyNot($workflow->hr_final_approver_employee_id);
        }

        return $query;
    }

    public function search(Employee $requester, ApprovalWorkflow $workflow, ?string $term = null, int $limit = 20)
    {
        $term = trim((string) $term);

        return $this->eligibleQuery($requester, $workflow)
            ->when($term !== '', function (Builder $query) use ($term) {
                $like = '%'.addcslashes($term, '%_\\').'%';
                $query->where(function (Builder $query) use ($like) {
                    $query->where('employee_no', 'like', $like)
                        ->orWhere('first_name', 'like', $like)
                        ->orWhere('last_name', 'like', $like)
                        ->orWhereHas('position', fn (Builder $q) => $q->where('position_name', 'like', $like))
                        ->orWhereHas('department', fn (Builder $q) => $q->where('department_name', 'like', $like));
                });
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->limit(min(max($limit, 1), 50))
            ->get();
    }

    public function suggestions(Employee $requester, ApprovalWorkflow $workflow)
    {
        $suggestions = collect();

        foreach ([
            'Immediate Supervisor' => $requester->immediateSupervisor,
            'Department Head' => $requester->departmentHead,
        ] as $reason => $employee) {
            if (! $employee || $suggestions->contains('id', $employee->id)) {
                continue;
            }

            if ($this->eligibleQuery($requester, $workflow)->whereKey($employee->id)->exists()) {
                $employee->loadMissing(['department', 'position']);
                $suggestions->push(['employee' => $employee, 'reason' => $reason]);
            }
        }

        return $suggestions;
    }

    public function validateSelection(Employee $requester, ApprovalWorkflow $workflow, array $ids): array
    {
        $ids = array_map('intval', array_values($ids));

        if (count($ids) !== count(array_unique($ids))) {
            throw ValidationException::withMessages(['approvers' => 'Approvers must be unique.']);
        }

        if (count($ids) < $workflow->min_approvers || count($ids) > $workflow->max_approvers) {
            throw ValidationException::withMessages([
                'approvers' => "Select between {$workflow->min_approvers} and {$workflow->max_approvers} approvers.",
            ]);
        }

        $approvers = $this->eligibleQuery($requester, $workflow)->whereIn('employees.id', $ids)->get()->keyBy('id');

        if ($approvers->count() !== count($ids)) {
            throw ValidationException::withMessages([
                'approvers' => 'Every selected approver must be active and eligible for this request.',
            ]);
        }

        return collect($ids)->map(fn (int $id) => $approvers->get($id))->all();
    }
}
