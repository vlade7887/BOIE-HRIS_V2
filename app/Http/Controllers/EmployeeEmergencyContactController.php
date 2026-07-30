<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmployeeEmergencyContactRequest;
use App\Http\Requests\UpdateEmployeeEmergencyContactRequest;
use App\Models\EmployeeEmergencyContact;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EmployeeEmergencyContactController extends Controller
{
    public function index(): View
    {
        $employeeEmergencyContacts = EmployeeEmergencyContact::with('employee')->latest()->get();

        return view('employee-emergency-contacts.index', compact('employeeEmergencyContacts'));
    }

    public function create(): View
    {
        $employeeEmergencyContact = new EmployeeEmergencyContact();

        return view('employee-emergency-contacts.create', compact('employeeEmergencyContact'));
    }

    public function store(StoreEmployeeEmergencyContactRequest $request): RedirectResponse
    {
        EmployeeEmergencyContact::create($request->validated());

        return redirect()->route('employee-emergency-contacts.index')->with('status', 'Employee emergency contact created successfully.');
    }

    public function show(EmployeeEmergencyContact $employeeEmergencyContact): View
    {
        return view('employee-emergency-contacts.show', compact('employeeEmergencyContact'));
    }

    public function edit(EmployeeEmergencyContact $employeeEmergencyContact): View
    {
        return view('employee-emergency-contacts.edit', compact('employeeEmergencyContact'));
    }

    public function update(UpdateEmployeeEmergencyContactRequest $request, EmployeeEmergencyContact $employeeEmergencyContact): RedirectResponse
    {
        $employeeEmergencyContact->update($request->validated());

        return redirect()->route('employee-emergency-contacts.index')->with('status', 'Employee emergency contact updated successfully.');
    }

    public function destroy(EmployeeEmergencyContact $employeeEmergencyContact): RedirectResponse
    {
        $employeeEmergencyContact->delete();

        return redirect()->route('employee-emergency-contacts.index')->with('status', 'Employee emergency contact archived successfully.');
    }
}
