@extends('layouts.adminlte')
@section('title', 'Department Master Data')
@section('page_title', 'Department Master Data')
@section('breadcrumb')<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item active">Department</li>@endsection
@section('content')
    <div class="container-fluid"><div class="card"><div class="card-header d-flex justify-content-end align-items-center"><a href="{{ route('departments.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>New Department</a></div><div class="card-body">
        @include('partials.master-data.search-toolbar', ['routePrefix' => 'departments'])
        @include('partials.master-data.index-table', ['records' => $departments, 'routePrefix' => 'departments', 'codeField' => 'department_code', 'nameField' => 'department_name', 'emptyLabel' => 'departments', 'archiveLabel' => 'department'])
    </div></div></div>
@endsection
