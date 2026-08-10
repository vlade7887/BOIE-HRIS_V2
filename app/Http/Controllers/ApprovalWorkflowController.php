<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreApprovalWorkflowRequest;
use App\Http\Requests\UpdateApprovalWorkflowRequest;
use App\Models\ApprovalWorkflow;
use App\Models\Employee;
use App\Services\ApprovalWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApprovalWorkflowController extends Controller
{
    public function index(): View
    {
        $approvalWorkflows = ApprovalWorkflow::query()
            ->with('hrFinalApprover')
            ->latest()
            ->get();

        return view(
            'approval-workflows.index',
            compact('approvalWorkflows')
        );
    }

    public function create(): View
    {
        $approvalWorkflow = new ApprovalWorkflow([
            'version' => 1,
            'status' => ApprovalWorkflow::STATUS_DRAFT,
            'min_approvers' => 1,
            'max_approvers' => 5,
            'hr_final_required' => true,
        ]);

        return view(
            'approval-workflows.create',
            compact('approvalWorkflow') + $this->formData()
        );
    }

    public function store(
        StoreApprovalWorkflowRequest $request,
        ApprovalWorkflowService $workflowService
    ): RedirectResponse {
        $workflow = $workflowService->create(
            $request->validated(),
            $request->user()
        );

        return redirect()
            ->route('approval-workflows.show', $workflow)
            ->with('success', 'Approval workflow created successfully.');
    }

    public function show(
        ApprovalWorkflow $approvalWorkflow
    ): View {
        $approvalWorkflow->load('hrFinalApprover');

        return view(
            'approval-workflows.show',
            compact('approvalWorkflow')
        );
    }

    public function edit(
        ApprovalWorkflow $approvalWorkflow
    ): View|RedirectResponse {
        if ($approvalWorkflow->status === 'active') {
            return redirect()
                ->route('approval-workflows.index')
                ->withErrors([
                    'workflow' => 'Active workflows cannot be edited directly. Create a new workflow version instead.',
                ]);
        }

        return view(
            'approval-workflows.edit',
            compact('approvalWorkflow') + $this->formData()
        );
    }

    public function update(
        UpdateApprovalWorkflowRequest $request,
        ApprovalWorkflow $approvalWorkflow,
        ApprovalWorkflowService $workflowService
    ): RedirectResponse {
        $workflow = $workflowService->update(
            $approvalWorkflow,
            $request->validated(),
            $request->user()
        );

        return redirect()
            ->route('approval-workflows.show', $workflow)
            ->with('success', 'Approval workflow updated successfully.');
    }

    public function archive(
        Request $request,
        ApprovalWorkflow $approvalWorkflow,
        ApprovalWorkflowService $workflowService
    ): RedirectResponse {
        $workflowService->delete(
            $approvalWorkflow,
            $request->user()
        );

        return redirect()
            ->route('approval-workflows.index')
            ->with('success', 'Approval workflow archived successfully.');
    }

    private function formData(): array
    {
        return [
            'eligibleApprovers' => Employee::query()
                ->where('is_active', true)
                ->where('can_approve_requests', true)
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get(),
        ];
    }
}
