<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmploymentStatusRequest;
use App\Http\Requests\UpdateEmploymentStatusRequest;
use App\Models\Employee;
use App\Models\EmploymentStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class EmploymentStatusController extends Controller
{
    public function index(): View
    {
        $search = request('search');
        $view = request('view');

        $query = EmploymentStatus::query();

        if ($view === 'archived') {
            $query->onlyTrashed();
        } else {
            $query->withoutTrashed();
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('status_code', 'like', "%{$search}%")
                    ->orWhere('status_name', 'like', "%{$search}%");
            });
        }

        $employmentStatuses = $query->latest()->paginate(10);

        return view('employment-statuses.index', compact('employmentStatuses', 'search', 'view'));
    }

    public function create(): View
    {
        $employmentStatus = new EmploymentStatus();

        return view('employment-statuses.create', compact('employmentStatus'));
    }

    public function store(StoreEmploymentStatusRequest $request): RedirectResponse
    {
        EmploymentStatus::create($request->validated());

        return Redirect::route('employment-statuses.index')->with('success', 'Employment status created successfully.');
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

        return Redirect::route('employment-statuses.index')->with('success', 'Employment status updated successfully.');
    }

    public function destroy(EmploymentStatus $employmentStatus): RedirectResponse
    {
        return $this->archive($employmentStatus);
    }

    public function archive(EmploymentStatus $employmentStatus): RedirectResponse
    {
        $hasActiveDependencies = Employee::query()
            ->where('employment_status_id', $employmentStatus->id)
            ->where('is_active', true)
            ->exists();

        if ($hasActiveDependencies) {
            return Redirect::back()->withErrors(['employmentStatus' => 'Cannot archive this employment status because active employees still reference it.']);
        }

        $employmentStatus->delete();

        return Redirect::route('employment-statuses.index')->with('success', 'Employment status archived successfully.');
    }

    public function restore(string $id): RedirectResponse
    {
        $employmentStatus = EmploymentStatus::withTrashed()->findOrFail($id);
        $employmentStatus->restore();

        return Redirect::route('employment-statuses.index')->with('success', 'Employment status restored successfully.');
    }
}
