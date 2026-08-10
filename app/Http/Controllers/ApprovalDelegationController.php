<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreApprovalDelegationRequest;
use App\Http\Requests\UpdateApprovalDelegationRequest;
use App\Models\ApprovalDelegation;
use App\Models\Department;
use App\Models\Employee;
use App\Services\ApprovalDelegationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApprovalDelegationController extends Controller
{
    public function index(): View
    {
        $approvalDelegations = ApprovalDelegation::query()
            ->with([
                'actingFor',
                'delegate',
                'department',
                'revokedByUser',
            ])
            ->latest()
            ->get();

        return view(
            'approval-delegations.index',
            compact('approvalDelegations')
        );
    }

    public function create(): View
    {
        $approvalDelegation = new ApprovalDelegation([
            'status' => ApprovalDelegation::STATUS_ACTIVE,
        ]);

        return view(
            'approval-delegations.create',
            $this->formData($approvalDelegation)
        );
    }

    public function store(
        StoreApprovalDelegationRequest $request,
        ApprovalDelegationService $delegationService
    ): RedirectResponse {
        $delegationService->create(
            $request->validated(),
            $request->user()
        );

        return redirect()
            ->route('approval-delegations.index')
            ->with('success', 'Approval delegation created successfully.');
    }

    public function edit(
        ApprovalDelegation $approvalDelegation
    ): View {
        return view(
            'approval-delegations.edit',
            $this->formData($approvalDelegation)
        );
    }

    public function update(
        UpdateApprovalDelegationRequest $request,
        ApprovalDelegation $approvalDelegation,
        ApprovalDelegationService $delegationService
    ): RedirectResponse {
        $delegationService->update(
            $approvalDelegation,
            $request->validated(),
            $request->user()
        );

        return redirect()
            ->route('approval-delegations.index')
            ->with('success', 'Approval delegation updated successfully.');
    }

    public function revoke(
        Request $request,
        ApprovalDelegation $approvalDelegation,
        ApprovalDelegationService $delegationService
    ): RedirectResponse {
        $delegationService->revoke(
            $approvalDelegation,
            $request->user()
        );

        return redirect()
            ->route('approval-delegations.index')
            ->with('success', 'Approval delegation revoked successfully.');
    }

    public function archive(
        Request $request,
        ApprovalDelegation $approvalDelegation,
        ApprovalDelegationService $delegationService
    ): RedirectResponse {
        $delegationService->delete(
            $approvalDelegation,
            $request->user()
        );

        return redirect()
            ->route('approval-delegations.index')
            ->with('success', 'Approval delegation archived successfully.');
    }

    private function formData(
        ApprovalDelegation $approvalDelegation
    ): array {
        return [
            'approvalDelegation' => $approvalDelegation,

            'employees' => Employee::query()
                ->where('is_active', true)
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get(),

            'departments' => Department::query()
                ->where('is_active', true)
                ->orderBy('department_name')
                ->get(),
        ];
    }
}
