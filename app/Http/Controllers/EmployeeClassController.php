<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmployeeClassRequest;
use App\Http\Requests\UpdateEmployeeClassRequest;
use App\Models\EmployeeClass;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EmployeeClassController extends Controller
{
    public function index(): View
    {
        $employeeClasses = EmployeeClass::latest()->get();

        return view('employee-classes.index', compact('employeeClasses'));
    }

    public function create(): View
    {
        $employeeClass = new EmployeeClass();

        return view('employee-classes.create', compact('employeeClass'));
    }

    public function store(StoreEmployeeClassRequest $request): RedirectResponse
    {
        EmployeeClass::create($request->validated());

        return redirect()->route('employee-classes.index')->with('status', 'Employee class created successfully.');
    }

    public function show(EmployeeClass $employeeClass): View
    {
        return view('employee-classes.show', compact('employeeClass'));
    }

    public function edit(EmployeeClass $employeeClass): View
    {
        return view('employee-classes.edit', compact('employeeClass'));
    }

    public function update(UpdateEmployeeClassRequest $request, EmployeeClass $employeeClass): RedirectResponse
    {
        $employeeClass->update($request->validated());

        return redirect()->route('employee-classes.index')->with('status', 'Employee class updated successfully.');
    }

    public function destroy(EmployeeClass $employeeClass): RedirectResponse
    {
        $employeeClass->delete();

        return redirect()->route('employee-classes.index')->with('status', 'Employee class archived successfully.');
    }
}
