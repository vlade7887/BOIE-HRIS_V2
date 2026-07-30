@extends('layouts.adminlte')

@section('title', 'Employee Management')

@section('page_title', 'Employee Management')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Employee List</li>
@endsection

@section('content')
<div class="card shadow-sm">
    <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <div>
            <h3 class="card-title mb-0">Employee List</h3>
            <p class="text-muted mb-0">Manage employee records.</p>
        </div>
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <a href="{{ route('employees.create') }}" class="btn btn-primary">+ Add Employee</a>
            <form class="d-flex gap-2" method="GET" action="{{ route('employees.index') }}">
                <input type="text" class="form-control" placeholder="Search" aria-label="Search">
                <button type="submit" class="btn btn-outline-secondary">Search</button>
            </form>
        </div>
    </div>

    <div class="card-body p-0">
        @if($employees->isEmpty())
            <div class="p-4 text-center text-muted">No employees found.</div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Employee No</th>
                            <th>Biometric ID</th>
                            <th>Employee Name</th>
                            <th>Department</th>
                            <th>Position</th>
                            <th>Employment Status</th>
                            <th>Employee Class</th>
                            <th>Status</th>
                            <th width="220">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employees as $employee)
                            <tr>
                                <td>{{ $employee->employee_no }}</td>
                                <td>{{ $employee->biometric_id ?? '—' }}</td>
                                <td>{{ trim(($employee->last_name ?? '') . ', ' . ($employee->first_name ?? '')) }}</td>
                                <td>{{ $employee->department?->department_name ?? '—' }}</td>
                                <td>{{ $employee->position?->position_name ?? '—' }}</td>
                                <td>{{ $employee->employmentStatus?->status_name ?? '—' }}</td>
                                <td>{{ $employee->employeeClass?->class_name ?? '—' }}</td>
                                <td>
                                    @if($employee->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('employees.show', $employee) }}" class="btn btn-outline-primary">View</a>
                                        <a href="{{ route('employees.edit', $employee) }}" class="btn btn-outline-secondary">Edit</a>
                                        <form action="{{ route('employees.destroy', $employee) }}" method="POST" onsubmit="return confirm('Archive this employee?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger">Archive</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
