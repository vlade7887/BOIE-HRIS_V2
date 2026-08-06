@extends('layouts.adminlte')
@section('title', 'Employee Class Master Data')
@section('page_title', 'Employee Class Master Data')
@section('breadcrumb')<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item active">Employee Class</li>@endsection
@section('content')
    <div class="container-fluid"><div class="card"><div class="card-header d-flex justify-content-between align-items-center"><h3 class="card-title mb-0">Employee Classes</h3><a href="{{ route('employee-classes.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>New Employee Class</a></div><div class="card-body">
        @include('partials.master-data.search-toolbar', ['routePrefix' => 'employee-classes'])
        @include('partials.master-data.index-table', ['records' => $employeeClasses, 'routePrefix' => 'employee-classes', 'codeField' => 'class_code', 'nameField' => 'class_name', 'emptyLabel' => 'employee classes', 'archiveLabel' => 'employee class'])
    </div></div></div>
@endsection
