<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmployeeGovernmentIdRequest;
use App\Http\Requests\UpdateEmployeeGovernmentIdRequest;
use App\Models\EmployeeGovernmentId;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EmployeeGovernmentIdController extends Controller
{
    public function index(): View
    {
        $employeeGovernmentIds = EmployeeGovernmentId::with('employee')->latest()->get();

        return view('employee-government-ids.index', compact('employeeGovernmentIds'));
    }

    public function create(): View
    {
        $employeeGovernmentId = new EmployeeGovernmentId();

        return view('employee-government-ids.create', compact('employeeGovernmentId'));
    }

    public function store(StoreEmployeeGovernmentIdRequest $request): RedirectResponse
    {
        EmployeeGovernmentId::create($request->validated());

        return redirect()->route('employee-government-ids.index')->with('status', 'Employee government ID created successfully.');
    }

    public function show(EmployeeGovernmentId $employeeGovernmentId): View
    {
        return view('employee-government-ids.show', compact('employeeGovernmentId'));
    }

    public function edit(EmployeeGovernmentId $employeeGovernmentId): View
    {
        return view('employee-government-ids.edit', compact('employeeGovernmentId'));
    }

    public function update(UpdateEmployeeGovernmentIdRequest $request, EmployeeGovernmentId $employeeGovernmentId): RedirectResponse
    {
        $employeeGovernmentId->update($request->validated());

        return redirect()->route('employee-government-ids.index')->with('status', 'Employee government ID updated successfully.');
    }

    public function destroy(EmployeeGovernmentId $employeeGovernmentId): RedirectResponse
    {
        $employeeGovernmentId->delete();

        return redirect()->route('employee-government-ids.index')->with('status', 'Employee government ID archived successfully.');
    }
}
