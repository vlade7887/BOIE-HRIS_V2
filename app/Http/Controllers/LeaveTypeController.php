<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeaveTypeRequest;
use App\Http\Requests\UpdateLeaveTypeRequest;
use App\Models\LeaveType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class LeaveTypeController extends Controller
{
    public function index(): View
    {
        $search = request('search');
        $view = request('view');
        $query = $view === 'archived' ? LeaveType::onlyTrashed() : LeaveType::query();

        if ($search) {
            $query->where(fn ($q) => $q->where('code', 'like', "%{$search}%")->orWhere('name', 'like', "%{$search}%"));
        }

        $leaveTypes = $query->latest()->paginate(10)->withQueryString();

        return view('leave-types.index', compact('leaveTypes', 'search', 'view'));
    }

    public function create(): View
    {
        return view('leave-types.create', ['leaveType' => new LeaveType(['allows_half_day' => true, 'is_active' => true])]);
    }

    public function store(StoreLeaveTypeRequest $request): RedirectResponse
    {
        LeaveType::create($request->validated());

        return Redirect::route('leave-types.index')->with('success', 'Leave type created successfully.');
    }

    public function show(LeaveType $leaveType): View
    {
        return view('leave-types.show', compact('leaveType'));
    }

    public function edit(LeaveType $leaveType): View
    {
        return view('leave-types.edit', compact('leaveType'));
    }

    public function update(UpdateLeaveTypeRequest $request, LeaveType $leaveType): RedirectResponse
    {
        $leaveType->update($request->validated());

        return Redirect::route('leave-types.index')->with('success', 'Leave type updated successfully.');
    }

    public function archive(LeaveType $leaveType): RedirectResponse
    {
        $leaveType->delete();

        return Redirect::route('leave-types.index')->with('success', 'Leave type archived successfully.');
    }

    public function restore(string $id): RedirectResponse
    {
        LeaveType::withTrashed()->findOrFail($id)->restore();

        return Redirect::route('leave-types.index')->with('success', 'Leave type restored successfully.');
    }
}
