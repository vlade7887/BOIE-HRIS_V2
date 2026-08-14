<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApprovalActionRequest;
use App\Http\Requests\CancelApprovalRequest;
use App\Http\Requests\RejectApprovalRequest;
use App\Models\ApprovalRequest;
use App\Services\ApprovalInboxService;
use App\Services\ApprovalRequestActionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;

class ApprovalInboxController extends Controller
{
    public function index(Request $request, ApprovalInboxService $inbox): View
    {
        try {
            return view('approval-inbox.index', $inbox->inbox($request->user()));
        } catch (ValidationException $exception) {
            return view('approval-inbox.unavailable', [
                'message' => $exception->errors()['actor'][0]
                    ?? 'The Approval Inbox is currently unavailable.',
            ]);
        }
    }

    public function show(Request $request, ApprovalRequest $approvalRequest, ApprovalInboxService $inbox): View
    {
        $data = $inbox->detail($request->user(), $approvalRequest);
        $data['actionKey'] = (string) Str::uuid();
        $data['cancelKey'] = (string) Str::uuid();

        return view('approval-inbox.show', $data);
    }

    public function approve(
        ApprovalActionRequest $request,
        ApprovalRequest $approvalRequest,
        ApprovalRequestActionService $actions
    ): RedirectResponse {
        $actions->approve(
            $approvalRequest,
            $request->user(),
            $request->validated('idempotency_key'),
            $request->validated('remarks')
        );

        $approvalRequest->refresh()->load('steps.canonicalApprover');
        $next = $approvalRequest->steps->firstWhere('status', 'pending')?->canonicalApprover;
        $message = $approvalRequest->status === ApprovalRequest::STATUS_APPROVED
            ? 'Request approved.'
            : 'Request approved. '.($next ? 'Next approver: '.$this->employeeName($next).'.' : 'The next approval step is now active.');

        return redirect()->route('approval-inbox.show', $approvalRequest)->with('success', $message);
    }

    public function reject(
        RejectApprovalRequest $request,
        ApprovalRequest $approvalRequest,
        ApprovalRequestActionService $actions
    ): RedirectResponse {
        $actions->reject(
            $approvalRequest,
            $request->user(),
            $request->validated('idempotency_key'),
            $request->validated('remarks')
        );

        return redirect()->route('approval-inbox.show', $approvalRequest)->with('success', 'Request rejected.');
    }

    public function cancel(
        CancelApprovalRequest $request,
        ApprovalRequest $approvalRequest,
        ApprovalRequestActionService $actions
    ): RedirectResponse {
        $actions->cancel(
            $approvalRequest,
            $request->user(),
            $request->validated('idempotency_key'),
            $request->validated('remarks')
        );

        return redirect()->route('approval-inbox.show', $approvalRequest)->with('success', 'Request cancelled.');
    }

    private function employeeName($employee): string
    {
        return trim($employee->first_name.' '.$employee->last_name);
    }
}
