@extends('layouts.adminlte')

@section('title', 'Company Master Data')
@section('page_title', 'Company Master Data')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Company</li>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">Companies</h3>
                <div class="d-flex gap-2 align-items-center">
                    <a href="{{ route('companies.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i>New Company
                    </a>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('companies.index') }}" class="row g-2 mb-3">
                    <div class="col-md-6">
                        <input type="text" name="search" class="form-control" value="{{ $search ?? '' }}" placeholder="Search by code or name">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-outline-primary w-100">Search</button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('companies.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('companies.index', ['view' => 'archived']) }}" class="btn btn-outline-dark w-100">Archived</a>
                    </div>
                </form>

                @if ($companies->isEmpty())
                    <div class="alert alert-light border">No companies found.</div>
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
                                @foreach ($companies as $company)
                                    <tr>
                                        <td>{{ $company->company_code }}</td>
                                        <td>{{ $company->company_name }}</td>
                                        <td>{{ $company->is_active ? 'Active' : 'Inactive' }}</td>
                                        <td class="text-end">
                                            <a href="{{ route('companies.show', $company) }}" class="btn btn-sm btn-info">View</a>
                                            <a href="{{ route('companies.edit', $company) }}" class="btn btn-sm btn-warning">Edit</a>
                                            @if ($company->trashed())
                                                <form action="{{ route('companies.restore', $company->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success">Restore</button>
                                                </form>
                                            @else
                                                <form action="{{ route('companies.archive', $company) }}" method="POST" class="d-inline" onsubmit="return confirm('Archive this company?');">
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
                        {{ $companies->appends(['search' => $search, 'view' => $view])->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
