<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmployeeDocumentRequest;
use App\Http\Requests\UpdateEmployeeDocumentRequest;
use App\Models\EmployeeDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EmployeeDocumentController extends Controller
{
    public function index(): View
    {
        $employeeDocuments = EmployeeDocument::with('employee')->latest()->get();

        return view('employee-documents.index', compact('employeeDocuments'));
    }

    public function create(): View
    {
        $employeeDocument = new EmployeeDocument();

        return view('employee-documents.create', compact('employeeDocument'));
    }

    public function store(StoreEmployeeDocumentRequest $request): RedirectResponse
    {
        EmployeeDocument::create($request->validated());

        return redirect()->route('employee-documents.index')->with('status', 'Employee document created successfully.');
    }

    public function show(EmployeeDocument $employeeDocument): View
    {
        return view('employee-documents.show', compact('employeeDocument'));
    }

    public function edit(EmployeeDocument $employeeDocument): View
    {
        return view('employee-documents.edit', compact('employeeDocument'));
    }

    public function update(UpdateEmployeeDocumentRequest $request, EmployeeDocument $employeeDocument): RedirectResponse
    {
        $employeeDocument->update($request->validated());

        return redirect()->route('employee-documents.index')->with('status', 'Employee document updated successfully.');
    }

    public function destroy(EmployeeDocument $employeeDocument): RedirectResponse
    {
        $employeeDocument->delete();

        return redirect()->route('employee-documents.index')->with('status', 'Employee document archived successfully.');
    }
}
