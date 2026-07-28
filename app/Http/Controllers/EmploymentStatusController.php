<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmploymentStatusRequest;
use App\Http\Requests\UpdateEmploymentStatusRequest;
use App\Models\EmploymentStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EmploymentStatusController extends Controller
{
    public function index(): View
    {
        $employmentStatuses = EmploymentStatus::latest()->get();

        return view('employment-statuses.index', compact('employmentStatuses'));
    }

    public function create(): View
    {
        $employmentStatus = new EmploymentStatus();

        return view('employment-statuses.create', compact('employmentStatus'));
    }

    public function store(StoreEmploymentStatusRequest $request): RedirectResponse
    {
        EmploymentStatus::create($request->validated());

        return redirect()->route('employment-statuses.index')->with('status', 'Employment status created successfully.');
    }

    public function show(EmploymentStatus $employmentStatus): View
    {
        return view('employment-statuses.show', compact('employmentStatus'));
    }

    public function edit(EmploymentStatus $employmentStatus): View
    {
        return view('employment-statuses.edit', compact('employmentStatus'));
    }

    public function update(UpdateEmploymentStatusRequest $request, EmploymentStatus $employmentStatus): RedirectResponse
    {
        $employmentStatus->update($request->validated());

        return redirect()->route('employment-statuses.index')->with('status', 'Employment status updated successfully.');
    }

    public function destroy(EmploymentStatus $employmentStatus): RedirectResponse
    {
        $employmentStatus->delete();

        return redirect()->route('employment-statuses.index')->with('status', 'Employment status archived successfully.');
    }
}
