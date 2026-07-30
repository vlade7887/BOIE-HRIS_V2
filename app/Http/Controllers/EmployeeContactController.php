<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmployeeContactRequest;
use App\Http\Requests\UpdateEmployeeContactRequest;
use App\Models\EmployeeContact;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EmployeeContactController extends Controller
{
    public function index(): View
    {
        $employeeContacts = EmployeeContact::with('employee')->latest()->get();

        return view('employee-contacts.index', compact('employeeContacts'));
    }

    public function create(): View
    {
        $employeeContact = new EmployeeContact();

        return view('employee-contacts.create', compact('employeeContact'));
    }

    public function store(StoreEmployeeContactRequest $request): RedirectResponse
    {
        EmployeeContact::create($request->validated());

        return redirect()->route('employee-contacts.index')->with('status', 'Employee contact created successfully.');
    }

    public function show(EmployeeContact $employeeContact): View
    {
        return view('employee-contacts.show', compact('employeeContact'));
    }

    public function edit(EmployeeContact $employeeContact): View
    {
        return view('employee-contacts.edit', compact('employeeContact'));
    }

    public function update(UpdateEmployeeContactRequest $request, EmployeeContact $employeeContact): RedirectResponse
    {
        $employeeContact->update($request->validated());

        return redirect()->route('employee-contacts.index')->with('status', 'Employee contact updated successfully.');
    }

    public function destroy(EmployeeContact $employeeContact): RedirectResponse
    {
        $employeeContact->delete();

        return redirect()->route('employee-contacts.index')->with('status', 'Employee contact archived successfully.');
    }
}
