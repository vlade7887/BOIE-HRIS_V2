@extends('layouts.adminlte')

@section('title', 'Section Master Data')
@section('page_title', 'Section Master Data')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Section</li>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">Sections</h3>
                <a href="{{ route('sections.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>New Section</a>
            </div>
            <div class="card-body">
                @if ($errors->any())<div class="alert alert-danger"><strong>{{ $errors->first() }}</strong></div>@endif
                @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
                <form method="GET" action="{{ route('sections.index') }}" class="row g-2 mb-3">
                    <div class="col-md-6"><input type="text" name="search" class="form-control" value="{{ $search ?? '' }}" placeholder="Search by code or name"></div>
                    <div class="col-md-2"><button type="submit" class="btn btn-outline-primary w-100">Search</button></div>
                    <div class="col-md-2"><a href="{{ route('sections.index') }}" class="btn btn-outline-secondary w-100">Reset</a></div>
                    <div class="col-md-2"><a href="{{ route('sections.index', ['view' => 'archived']) }}" class="btn btn-outline-dark w-100">Archived</a></div>
                </form>
                @if ($sections->isEmpty())
                    <div class="alert alert-light border">No sections found.</div>
                @else
                    <div class="table-responsive"><table class="table table-bordered table-hover mb-0"><thead><tr><th>Section Code</th><th>Section Name</th><th>Active Status</th><th class="text-end">Actions</th></tr></thead><tbody>
                        @foreach ($sections as $section)
                            <tr><td>{{ $section->section_code }}</td><td>{{ $section->section_name }}</td><td>{{ $section->is_active ? 'Active' : 'Inactive' }}</td><td class="text-end"><a href="{{ route('sections.show', $section) }}" class="btn btn-sm btn-info">View</a> <a href="{{ route('sections.edit', $section) }}" class="btn btn-sm btn-warning">Edit</a> @if ($section->trashed())<form action="{{ route('sections.restore', $section->id) }}" method="POST" class="d-inline">@csrf<button type="submit" class="btn btn-sm btn-success">Restore</button></form>@else<form action="{{ route('sections.archive', $section) }}" method="POST" class="d-inline" onsubmit="return confirm('Archive this section?');">@csrf<button type="submit" class="btn btn-sm btn-danger">Archive</button></form>@endif</td></tr>
                        @endforeach
                    </tbody></table></div>
                    <div class="mt-3">{{ $sections->appends(['search' => $search, 'view' => $view])->links() }}</div>
                @endif
            </div>
        </div>
    </div>
@endsection
