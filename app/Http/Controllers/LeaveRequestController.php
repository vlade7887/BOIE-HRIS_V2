<?php

namespace App\Http\Controllers;

use App\Http\Requests\LeaveDraftRequest;
use App\Models\LeaveType;
use App\Services\ApproverPickerService;
use App\Services\LeaveRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeaveRequestController extends Controller
{
    public function create(Request $request, ApproverPickerService $picker): View
    {
        $requester = $picker->requester($request->user());
        $leaveTypes = LeaveType::query()->where('is_active', true)->orderBy('name')->get();

        return view('leave-requests.create', [
            'requester' => $requester,
            'leaveTypes' => $leaveTypes,
            'preview' => null,
        ]);
    }

    public function preview(LeaveDraftRequest $request, ApproverPickerService $picker, LeaveRequestService $leaveRequests): View
    {
        $requester = $picker->requester($request->user());
        $preview = $leaveRequests->preview($requester, $request->validated());
        $leaveTypes = LeaveType::query()->where('is_active', true)->orderBy('name')->get();

        return view('leave-requests.create', compact('requester', 'leaveTypes', 'preview'));
    }

    public function previewFallback(): RedirectResponse
    {
        return redirect()->route('leave-requests.create');
    }

    public function storeDraft(LeaveDraftRequest $request, ApproverPickerService $picker, LeaveRequestService $leaveRequests): RedirectResponse
    {
        $requester = $picker->requester($request->user());
        $draft = $leaveRequests->saveDraft($requester, $request->validated());

        return redirect()->route('leave-requests.create')->with('success', "Leave draft #{$draft->id} saved. Approval submission will be added in the next slice.");
    }
}
