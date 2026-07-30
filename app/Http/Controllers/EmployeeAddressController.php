<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmployeeAddressRequest;
use App\Http\Requests\UpdateEmployeeAddressRequest;
use App\Models\EmployeeAddress;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EmployeeAddressController extends Controller
{
    public function index(): View
    {
        $employeeAddresses = EmployeeAddress::with('employee')->latest()->get();

        return view('employee-addresses.index', compact('employeeAddresses'));
    }

    public function create(): View
    {
        $employeeAddress = new EmployeeAddress();

        return view('employee-addresses.create', compact('employeeAddress'));
    }

    public function store(StoreEmployeeAddressRequest $request): RedirectResponse
    {
        EmployeeAddress::create($request->validated());

        return redirect()->route('employee-addresses.index')->with('status', 'Employee address created successfully.');
    }

    public function show(EmployeeAddress $employeeAddress): View
    {
        return view('employee-addresses.show', compact('employeeAddress'));
    }

    public function edit(EmployeeAddress $employeeAddress): View
    {
        return view('employee-addresses.edit', compact('employeeAddress'));
    }

    public function update(UpdateEmployeeAddressRequest $request, EmployeeAddress $employeeAddress): RedirectResponse
    {
        $employeeAddress->update($request->validated());

        return redirect()->route('employee-addresses.index')->with('status', 'Employee address updated successfully.');
    }

    public function destroy(EmployeeAddress $employeeAddress): RedirectResponse
    {
        $employeeAddress->delete();

        return redirect()->route('employee-addresses.index')->with('status', 'Employee address archived successfully.');
    }
}
