@extends('layouts.adminlte')

@section('title', 'Employment Status Master Data')
@section('page_title', 'Employment Status Master Data')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Employment Status</li>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">Employment Statuses</h3>
                <a href="{{ route('employment-statuses.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i>New Employment Status
                </a>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('employment-statuses.index') }}" class="row g-2 mb-3">
                    <div class="col-md-6">
                        <input type="text" name="search" class="form-control" value="{{ $search ?? '' }}" placeholder="Search by code or name">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-outline-primary w-100">Search</button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('employment-statuses.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('employment-statuses.index', ['view' => 'archived']) }}" class="btn btn-outline-dark w-100">Archived</a>
                    </div>
                </form>

                @if ($employmentStatuses->isEmpty())
                    <div class="alert alert-light border">No employment statuses found.</div>
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
                                @foreach ($employmentStatuses as $employmentStatus)
                                    <tr>
                                        <td>{{ $employmentStatus->status_code }}</td>
                                        <td>{{ $employmentStatus->status_name }}</td>
                                        <td>{{ $employmentStatus->is_active ? 'Active' : 'Inactive' }}</td>
                                        <td class="text-end">
                                            <a href="{{ route('employment-statuses.show', $employmentStatus) }}" class="btn btn-sm btn-info">View</a>
                                            <a href="{{ route('employment-statuses.edit', $employmentStatus) }}" class="btn btn-sm btn-warning">Edit</a>
                                            @if ($employmentStatus->trashed())
                                                <form action="{{ route('employment-statuses.restore', $employmentStatus->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success">Restore</button>
                                                </form>
                                            @else
                                                <form action="{{ route('employment-statuses.archive', $employmentStatus) }}" method="POST" class="d-inline" onsubmit="return confirm('Archive this employment status?');">
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
                        {{ $employmentStatuses->appends(['search' => $search, 'view' => $view])->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
