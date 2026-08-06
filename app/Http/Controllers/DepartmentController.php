<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDepartmentRequest;
use App\Http\Requests\UpdateDepartmentRequest;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    public function index(): View
    {
        $search = request('search');
        $view = request('view');

        $query = Department::with('unit');

        if ($view === 'archived') {
            $query->onlyTrashed();
        } else {
            $query->withoutTrashed();
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('department_code', 'like', "%{$search}%")
                    ->orWhere('department_name', 'like', "%{$search}%");
            });
        }

        $departments = $query->latest()->paginate(10);

        return view('departments.index', compact('departments', 'search', 'view'));
    }

    public function create(): View
    {
        $department = new Department();
        $units = Unit::query()->where('is_active', true)->orderBy('unit_name')->get();

        return view('departments.create', compact('department', 'units'));
    }

    public function store(StoreDepartmentRequest $request): RedirectResponse
    {
        Department::create($request->validated());

        return Redirect::route('departments.index')->with('success', 'Department created successfully.');
    }

    public function show(Department $department): View
    {
        return view('departments.show', compact('department'));
    }

    public function edit(Department $department): View
    {
        $units = Unit::query()->where('is_active', true)->orderBy('unit_name')->get();

        return view('departments.edit', compact('department', 'units'));
    }

    public function update(UpdateDepartmentRequest $request, Department $department): RedirectResponse
    {
        $department->update($request->validated());

        return Redirect::route('departments.index')->with('success', 'Department updated successfully.');
    }

    public function archive(Department $department): RedirectResponse
    {
        $hasActiveDependencies = Employee::query()
            ->where('department_id', $department->id)
            ->where('is_active', true)
            ->exists();

        if ($hasActiveDependencies) {
            return Redirect::back()->withErrors(['department' => 'Cannot archive this department because active employees still reference it.']);
        }

        $department->delete();

        return Redirect::route('departments.index')->with('success', 'Department archived successfully.');
    }

    public function restore(string $id): RedirectResponse
    {
        $department = Department::withTrashed()->findOrFail($id);
        $department->restore();

        return Redirect::route('departments.index')->with('success', 'Department restored successfully.');
    }
}
