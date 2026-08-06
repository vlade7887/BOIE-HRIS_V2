@extends('layouts.adminlte')
@section('title', 'Employment Status Master Data')
@section('page_title', 'Employment Status Master Data')
@section('breadcrumb')<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item active">Employment Status</li>@endsection
@section('content')
    <div class="container-fluid"><div class="card"><div class="card-header d-flex justify-content-between align-items-center"><h3 class="card-title mb-0">Employment Statuses</h3><a href="{{ route('employment-statuses.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>New Employment Status</a></div><div class="card-body">
        @include('partials.master-data.search-toolbar', ['routePrefix' => 'employment-statuses'])
        @include('partials.master-data.index-table', ['records' => $employmentStatuses, 'routePrefix' => 'employment-statuses', 'codeField' => 'status_code', 'nameField' => 'status_name', 'emptyLabel' => 'employment statuses', 'archiveLabel' => 'employment status'])
    </div></div></div>
@endsection
