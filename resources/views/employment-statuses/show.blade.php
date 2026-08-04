@extends('layouts.adminlte')

@section('title', 'Employment Status Details')
@section('page_title', 'Employment Status Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('employment-statuses.index') }}">Employment Status</a></li>
    <li class="breadcrumb-item active">Details</li>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">{{ $employmentStatus->status_name }}</h3>
                <div class="d-flex gap-2">
                    <a href="{{ route('employment-statuses.edit', $employmentStatus) }}" class="btn btn-warning btn-sm">Edit</a>
                    <a href="{{ route('employment-statuses.index') }}" class="btn btn-secondary btn-sm">Back</a>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                            <h6 class="text-muted mb-3">Basic Information</h6>
                            <dl class="row mb-0">
                                <dt class="col-sm-4">Code</dt>
                                <dd class="col-sm-8">{{ $employmentStatus->status_code ?? '—' }}</dd>
                                <dt class="col-sm-4">Name</dt>
                                <dd class="col-sm-8">{{ $employmentStatus->status_name ?? '—' }}</dd>
                                <dt class="col-sm-4">Status</dt>
                                <dd class="col-sm-8">{{ $employmentStatus->is_active ? 'Active' : 'Inactive' }}</dd>
                            </dl>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                            <h6 class="text-muted mb-3">Remarks</h6>
                            <p class="mb-0">{{ $employmentStatus->remarks ?? '—' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
