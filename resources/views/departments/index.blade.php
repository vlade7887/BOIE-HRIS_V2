@extends('layouts.adminlte')

@section('title', 'Department Master Data')
@section('page_title', 'Department Master Data')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Department</li>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">Departments</h3>
                <a href="{{ route('departments.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>New Department</a>
            </div>
            <div class="card-body">
                @if ($errors->any())<div class="alert alert-danger"><strong>{{ $errors->first() }}</strong></div>@endif
                @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
                <form method="GET" action="{{ route('departments.index') }}" class="row g-2 mb-3">
                    <div class="col-md-6"><input type="text" name="search" class="form-control" value="{{ $search ?? '' }}" placeholder="Search by code or name"></div>
                    <div class="col-md-2"><button type="submit" class="btn btn-outline-primary w-100">Search</button></div>
                    <div class="col-md-2"><a href="{{ route('departments.index') }}" class="btn btn-outline-secondary w-100">Reset</a></div>
                    <div class="col-md-2"><a href="{{ route('departments.index', ['view' => 'archived']) }}" class="btn btn-outline-dark w-100">Archived</a></div>
                </form>
                @if ($departments->isEmpty())
                    <div class="alert alert-light border">No departments found.</div>
                @else
                    <div class="table-responsive"><table class="table table-bordered table-hover mb-0"><thead><tr><th>Department Code</th><th>Department Name</th><th>Active Status</th><th class="text-end">Actions</th></tr></thead><tbody>
                        @foreach ($departments as $department)
                            <tr><td>{{ $department->department_code }}</td><td>{{ $department->department_name }}</td><td>{{ $department->is_active ? 'Active' : 'Inactive' }}</td><td class="text-end"><a href="{{ route('departments.show', $department) }}" class="btn btn-sm btn-info">View</a> <a href="{{ route('departments.edit', $department) }}" class="btn btn-sm btn-warning">Edit</a> @if ($department->trashed())<form action="{{ route('departments.restore', $department->id) }}" method="POST" class="d-inline">@csrf<button type="submit" class="btn btn-sm btn-success">Restore</button></form>@else<form action="{{ route('departments.archive', $department) }}" method="POST" class="d-inline" onsubmit="return confirm('Archive this department?');">@csrf<button type="submit" class="btn btn-sm btn-danger">Archive</button></form>@endif</td></tr>
                        @endforeach
                    </tbody></table></div>
                    <div class="mt-3">{{ $departments->appends(['search' => $search, 'view' => $view])->links() }}</div>
                @endif
            </div>
        </div>
    </div>
@endsection
