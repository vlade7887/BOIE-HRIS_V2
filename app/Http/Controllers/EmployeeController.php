<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Base;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeClass;
use App\Models\EmploymentStatus;
use App\Models\Position;
use App\Models\Section;
use App\Models\Unit;
use App\Services\EmployeeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function index(): View
    {
        $employees = Employee::with([
            'company',
            'base',
            'unit',
            'department',
            'section',
            'position',
            'employmentStatus',
            'employeeClass',
        ])->latest()->get();

        return view('employees.index', compact('employees'));
    }

    public function create(): View
    {
        $employee = new Employee();
        $formData = $this->formData();

        return view('employees.create', compact('employee') + $formData);
    }

    public function store(StoreEmployeeRequest $request, EmployeeService $employeeService): RedirectResponse
    {
        $employee = $employeeService->create($request->validated());

        return redirect()->route('employees.edit', $employee)->with('status', 'Employee created successfully.');
    }

    public function show(Employee $employee): View
    {
        return view('employees.show', compact('employee'));
    }

    public function edit(Employee $employee): View
    {
        $employee->load(['employeeContact', 'employeeAddress', 'employeeGovernmentId']);
        $formData = $this->formData($employee);

        return view('employees.edit', compact('employee') + $formData);
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee, EmployeeService $employeeService): RedirectResponse
    {
        $employeeService->update($employee, $request->validated());

        return redirect()->route('employees.edit', $employee)->with('status', 'Employee updated successfully.');
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        $employee->delete();

        return redirect()->route('employees.index')->with('status', 'Employee archived successfully.');
    }

    private function formData(?Employee $employee = null): array
    {
        $companies = Company::orderBy('company_name')->get();
        $bases = Base::orderBy('base_name')->get();
        $units = Unit::orderBy('unit_name')->get();
        $departments = Department::orderBy('department_name')->get();
        $sections = Section::orderBy('section_name')->get();
        $positions = Position::orderBy('position_name')->get();
        $employmentStatuses = EmploymentStatus::orderBy('status_name')->get();
        $employeeClasses = EmployeeClass::orderBy('class_name')->get();
        $supervisors = Employee::when($employee, fn ($query) => $query->whereKeyNot($employee->id))
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        return compact(
            'employee',
            'companies',
            'bases',
            'units',
            'departments',
            'sections',
            'positions',
            'employmentStatuses',
            'employeeClasses',
            'supervisors'
        );
    }
}
