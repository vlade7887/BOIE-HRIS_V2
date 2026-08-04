@extends('layouts.adminlte')

@section('title', 'Employee Class Master Data')
@section('page_title', 'Employee Class Master Data')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Employee Class</li>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">Employee Classes</h3>
                <a href="{{ route('employee-classes.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i>New Employee Class
                </a>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('employee-classes.index') }}" class="row g-2 mb-3">
                    <div class="col-md-6">
                        <input type="text" name="search" class="form-control" value="{{ $search ?? '' }}" placeholder="Search by code or name">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-outline-primary w-100">Search</button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('employee-classes.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('employee-classes.index', ['view' => 'archived']) }}" class="btn btn-outline-dark w-100">Archived</a>
                    </div>
                </form>

                @if ($employeeClasses->isEmpty())
                    <div class="alert alert-light border">No employee classes found.</div>
                @else
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($employeeClasses as $employeeClass)
                                    <tr>
                                        <td>{{ $employeeClass->class_code }}</td>
                                        <td>{{ $employeeClass->class_name }}</td>
                                        <td>{{ $employeeClass->is_active ? 'Active' : 'Inactive' }}</td>
                                        <td class="text-end">
                                            <a href="{{ route('employee-classes.show', $employeeClass) }}" class="btn btn-sm btn-info">View</a>
                                            <a href="{{ route('employee-classes.edit', $employeeClass) }}" class="btn btn-sm btn-warning">Edit</a>
                                            @if ($employeeClass->trashed())
                                                <form action="{{ route('employee-classes.restore', $employeeClass->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success">Restore</button>
                                                </form>
                                            @else
                                                <form action="{{ route('employee-classes.archive', $employeeClass) }}" method="POST" class="d-inline" onsubmit="return confirm('Archive this employee class?');">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-danger">Archive</button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $employeeClasses->appends(['search' => $search, 'view' => $view])->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
