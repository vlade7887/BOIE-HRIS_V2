@extends('layouts.adminlte')

@section('title', 'Base Master Data')
@section('page_title', 'Base Master Data')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item active">Base</li>
@endsection

@section('content')
    <div class="container-fluid"><div class="card"><div class="card-header d-flex justify-content-between align-items-center"><h3 class="card-title mb-0">Bases</h3><a href="{{ route('bases.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>New Base</a></div><div class="card-body">
        @if ($errors->any())<div class="alert alert-danger"><strong>{{ $errors->first() }}</strong></div>@endif
        <form method="GET" action="{{ route('bases.index') }}" class="row g-2 mb-3"><div class="col-md-6"><input type="text" name="search" class="form-control" value="{{ $search ?? '' }}" placeholder="Search by code or name"></div><div class="col-md-2"><button type="submit" class="btn btn-outline-primary w-100">Search</button></div><div class="col-md-2"><a href="{{ route('bases.index') }}" class="btn btn-outline-secondary w-100">Reset</a></div><div class="col-md-2"><a href="{{ route('bases.index', ['view' => 'archived']) }}" class="btn btn-outline-dark w-100">Archived</a></div></form>
        @if ($bases->isEmpty())<div class="alert alert-light border">No bases found.</div>@else<div class="table-responsive"><table class="table table-bordered table-hover mb-0"><thead><tr><th>Code</th><th>Name</th><th>Status</th><th class="text-end">Actions</th></tr></thead><tbody>@foreach ($bases as $base)<tr><td>{{ $base->base_code }}</td><td>{{ $base->base_name }}</td><td>{{ $base->is_active ? 'Active' : 'Inactive' }}</td><td class="text-end"><a href="{{ route('bases.show', $base) }}" class="btn btn-sm btn-info">View</a><a href="{{ route('bases.edit', $base) }}" class="btn btn-sm btn-warning">Edit</a>@if ($base->trashed())<form action="{{ route('bases.restore', $base->id) }}" method="POST" class="d-inline">@csrf<button type="submit" class="btn btn-sm btn-success">Restore</button></form>@else<form action="{{ route('bases.archive', $base) }}" method="POST" class="d-inline" onsubmit="return confirm('Archive this base?');">@csrf<button type="submit" class="btn btn-sm btn-danger">Archive</button></form>@endif</td></tr>@endforeach</tbody></table></div><div class="mt-3">{{ $bases->appends(['search' => $search, 'view' => $view])->links() }}</div>@endif
    </div></div></div>
@endsection
