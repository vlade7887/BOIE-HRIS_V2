<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmployeeClassRequest;
use App\Http\Requests\UpdateEmployeeClassRequest;
use App\Models\Employee;
use App\Models\EmployeeClass;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class EmployeeClassController extends Controller
{
    public function index(): View
    {
        $search = request('search');
        $view = request('view');

        $query = EmployeeClass::query();

        if ($view === 'archived') {
            $query->onlyTrashed();
        } else {
            $query->withoutTrashed();
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('class_code', 'like', "%{$search}%")
                    ->orWhere('class_name', 'like', "%{$search}%");
            });
        }

        $employeeClasses = $query->latest()->paginate(10);

        return view('employee-classes.index', compact('employeeClasses', 'search', 'view'));
    }

    public function create(): View
    {
        $employeeClass = new EmployeeClass();

        return view('employee-classes.create', compact('employeeClass'));
    }

    public function store(StoreEmployeeClassRequest $request): RedirectResponse
    {
        EmployeeClass::create($request->validated());

        return Redirect::route('employee-classes.index')->with('success', 'Employee class created successfully.');
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

        return Redirect::route('employee-classes.index')->with('success', 'Employee class updated successfully.');
    }

    public function destroy(EmployeeClass $employeeClass): RedirectResponse
    {
        return $this->archive($employeeClass);
    }

    public function archive(EmployeeClass $employeeClass): RedirectResponse
    {
        $hasActiveDependencies = Employee::query()
            ->where('employee_class_id', $employeeClass->id)
            ->where('is_active', true)
            ->exists();

        if ($hasActiveDependencies) {
            return Redirect::back()->withErrors(['employeeClass' => 'Cannot archive this employee class because active employees still reference it.']);
        }

        $employeeClass->delete();

        return Redirect::route('employee-classes.index')->with('success', 'Employee class archived successfully.');
    }

    public function restore(string $id): RedirectResponse
    {
        $employeeClass = EmployeeClass::withTrashed()->findOrFail($id);
        $employeeClass->restore();

        return Redirect::route('employee-classes.index')->with('success', 'Employee class restored successfully.');
    }
}
