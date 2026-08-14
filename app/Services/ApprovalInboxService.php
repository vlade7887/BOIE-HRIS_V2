<?php

namespace App\Services;

use App\Models\ApprovalRequest;
use App\Models\ApprovalRequestStep;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class ApprovalInboxService
{
    public function __construct(
        private readonly ApprovalDelegationResolver $delegations
    ) {
    }

    public function employeeFor(User $user): Employee
    {
        $employee = Employee::withTrashed()->where('user_id', $user->id)->first();

        if (! $employee || $employee->trashed() || ! $employee->is_active) {
            throw ValidationException::withMessages([
                'actor' => 'Your account must be mapped to an active employee before using the Approval Inbox.',
            ]);
        }

        return $employee;
    }

    public function inbox(User $user): array
    {
        $employee = $this->employeeFor($user);
        $requests = ApprovalRequest::query()
            ->where('status', ApprovalRequest::STATUS_PENDING)
            ->whereHas('steps', function ($query) {
                $query->where('status', ApprovalRequestStep::STATUS_PENDING)
                    ->whereColumn('approval_request_steps.step_order', 'approval_requests.current_step_order');
            })
            ->with([
                'requester.department',
                'requestDepartment',
                'workflow',
                'steps' => fn ($query) => $query->where('status', ApprovalRequestStep::STATUS_PENDING),
            ])
            ->latest('submitted_at')
            ->get();

        return [
            'employee' => $employee,
            'items' => $requests->map(function (ApprovalRequest $request) use ($employee) {
                $context = $this->currentContext($request, $employee);

                return $context['authorized']
                    ? ['request' => $request, ...$context]
                    : null;
            })->filter()->values(),
        ];
    }

    public function detail(User $user, ApprovalRequest $request): array
    {
        $employee = $this->employeeFor($user);
        $request->load([
            'requester.department',
            'requestDepartment',
            'workflow',
            'steps.canonicalApprover.position',
            'actions.actorEmployee',
            'actions.canonicalApprover',
            'actions.actingFor',
            'actions.delegation',
        ]);

        $context = $this->currentContext($request, $employee);
        $canView = $request->requester_employee_id === $employee->id
            || $request->actions->contains(fn ($action) => $action->actor_employee_id === $employee->id)
            || $context['authorized'];

        abort_unless($canView, 403, 'You are not authorized to view this approval request.');

        return [
            'request' => $request,
            'employee' => $employee,
            ...$context,
            'canCancel' => $request->requester_employee_id === $employee->id
                && in_array($request->status, [ApprovalRequest::STATUS_DRAFT, ApprovalRequest::STATUS_PENDING], true),
        ];
    }

    private function currentContext(ApprovalRequest $request, Employee $employee): array
    {
        $step = $request->steps->first(fn ($step) =>
            $step->step_order === $request->current_step_order
            && $step->status === ApprovalRequestStep::STATUS_PENDING
        );

        if ($request->status !== ApprovalRequest::STATUS_PENDING || ! $step) {
            return [
                'authorized' => false,
                'direct' => false,
                'delegation' => null,
                'currentStep' => $step,
            ];
        }

        if ($request->requester_employee_id === $employee->id) {
            return [
                'authorized' => false,
                'direct' => false,
                'delegation' => null,
                'currentStep' => $step,
            ];
        }

        if ($step->canonical_approver_employee_id === $employee->id) {
            return [
                'authorized' => true,
                'direct' => true,
                'delegation' => null,
                'currentStep' => $step,
            ];
        }

        $delegation = $this->delegations->resolve($request, $step->canonicalApprover, $employee);

        return [
            'authorized' => $delegation !== null,
            'direct' => false,
            'delegation' => $delegation,
            'currentStep' => $step,
        ];
    }
}
