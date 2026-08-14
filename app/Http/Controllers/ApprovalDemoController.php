<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreApprovalDemoRequest;
use App\Models\ApprovalRequest;
use App\Services\ApprovalRequestSubmissionService;
use App\Services\ApproverPickerService;
use App\Support\ApprovalDemoApprovable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;

class ApprovalDemoController extends Controller
{
    public function create(Request $request, ApproverPickerService $picker): View|RedirectResponse
    {
        try {
            $requester = $picker->requester($request->user());
            $workflow = $picker->workflow('approval_demo');
        } catch (ValidationException $exception) {
            return view('approval-demo.unavailable', [
                'message' => $exception->errors()['workflow'][0]
                    ?? $exception->errors()['requester'][0]
                    ?? 'The Approval Demo is currently unavailable.',
            ]);
        }
        $this->seedDraft($request);

        return $this->formView($request, $picker, $requester, $workflow);
    }

    public function preview(Request $request, ApproverPickerService $picker): View
    {
        $requester = $picker->requester($request->user());
        $workflow = $picker->workflow('approval_demo');
        $this->assertDraft($request);
        $selected = $picker->validateSelection($requester, $workflow, (array) $request->input('approvers', []));

        return $this->formView($request, $picker, $requester, $workflow, $selected, true);
    }

    public function search(Request $request, ApproverPickerService $picker): JsonResponse
    {
        $requester = $picker->requester($request->user());
        $workflow = $picker->workflow('approval_demo');

        return response()->json($picker->search($requester, $workflow, $request->string('q')->toString())->map(
            fn ($employee) => $this->employeePayload($employee)
        )->values());
    }

    public function store(
        StoreApprovalDemoRequest $request,
        ApproverPickerService $picker,
        ApprovalRequestSubmissionService $submission
    ): RedirectResponse {
        $requester = $picker->requester($request->user());
        $workflow = $picker->workflow('approval_demo');
        $this->assertDraft($request);
        $picker->validateSelection($requester, $workflow, $request->validated('approvers'));

        $approvalRequest = $submission->submit(
            new ApprovalDemoApprovable((int) session('approval_demo.id'), $requester),
            $request->validated('approvers'),
            $request->user(),
            $request->validated('idempotency_key')
        );

        return redirect()->route('approval-demo.show', $approvalRequest);
    }

    public function show(Request $request, ApprovalRequest $approvalRequest, ApproverPickerService $picker): View
    {
        $requester = $picker->requester($request->user());

        abort_unless(
            $approvalRequest->module_key === 'approval_demo' &&
            $approvalRequest->requester_employee_id === $requester->id,
            404
        );

        $approvalRequest->load([
            'requester.department',
            'workflow.hrFinalApprover.position',
            'requestDepartment',
            'steps.canonicalApprover.position',
        ]);

        return view('approval-demo.show', compact('approvalRequest'));
    }

    private function formView(
        Request $request,
        ApproverPickerService $picker,
        $requester,
        $workflow,
        array $selected = [],
        bool $preview = false
    ): View {
        return view('approval-demo.create', [
            'requester' => $requester,
            'workflow' => $workflow,
            'suggestions' => $picker->suggestions($requester, $workflow),
            'selected' => $selected,
            'preview' => $preview,
            'demoId' => session('approval_demo.id'),
            'idempotencyKey' => session('approval_demo.idempotency_key'),
        ]);
    }

    private function seedDraft(Request $request): void
    {
        if (! $request->session()->has('approval_demo.id')) {
            $request->session()->put('approval_demo', [
                'id' => random_int(1, PHP_INT_MAX),
                'idempotency_key' => bin2hex(random_bytes(16)),
            ]);
        }
    }

    private function assertDraft(Request $request): void
    {
        $this->seedDraft($request);

        if ((int) $request->input('demo_id') !== (int) session('approval_demo.id')) {
            abort(422, 'This demo filing session is no longer valid. Reload the filing page.');
        }
    }

    private function employeePayload($employee): array
    {
        return [
            'id' => $employee->id,
            'employee_no' => $employee->employee_no,
            'name' => trim($employee->first_name.' '.$employee->last_name),
            'position' => $employee->position?->position_name,
            'department' => $employee->department?->department_name,
        ];
    }
}
