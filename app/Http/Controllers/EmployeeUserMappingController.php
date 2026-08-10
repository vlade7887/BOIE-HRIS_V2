<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateEmployeeUserMappingRequest;
use App\Models\Employee;
use App\Models\User;
use App\Services\EmployeeUserMappingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeUserMappingController extends Controller
{
    public function index(): View
    {
        $employees = Employee::query()
            ->with('user')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        return view(
            'employee-user-mappings.index',
            compact('employees')
        );
    }

    public function edit(Employee $employee): View
    {
        $users = User::query()
            ->where(function ($query) use ($employee) {
                $query
                    ->whereDoesntHave('employee')
                    ->when(
                        $employee->user_id,
                        fn ($query) => $query->orWhere('users.id', $employee->user_id)
                    );
            })
            ->orderBy('name')
            ->get();

        return view(
            'employee-user-mappings.edit',
            compact('employee', 'users')
        );
    }

    public function update(
        UpdateEmployeeUserMappingRequest $request,
        Employee $employee,
        EmployeeUserMappingService $mappingService
    ): RedirectResponse {
        $userId = $request->validated('user_id');

        $user = $userId
            ? User::findOrFail($userId)
            : null;

        $mappingService->map(
            $employee,
            $user,
            $request->user()
        );

        return redirect()
            ->route('employee-user-mappings.index')
            ->with('success', 'Employee user mapping updated successfully.');
    }

    public function unmap(
        Request $request,
        Employee $employee,
        EmployeeUserMappingService $mappingService
    ): RedirectResponse {
        $mappingService->map(
            $employee,
            null,
            $request->user()
        );

        return redirect()
            ->route('employee-user-mappings.index')
            ->with('success', 'Employee user mapping removed successfully.');
    }
}
