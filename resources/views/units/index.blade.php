@extends('layouts.adminlte')
@section('title', 'Unit Master Data')
@section('page_title', 'Unit Master Data')
@section('breadcrumb')<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item active">Unit</li>@endsection
@section('content')
    <div class="container-fluid"><div class="card"><div class="card-header d-flex justify-content-end align-items-center"><a href="{{ route('units.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>New Unit</a></div><div class="card-body">
        @include('partials.master-data.search-toolbar', ['routePrefix' => 'units'])
        @include('partials.master-data.index-table', ['records' => $units, 'routePrefix' => 'units', 'codeField' => 'unit_code', 'nameField' => 'unit_name', 'emptyLabel' => 'units', 'archiveLabel' => 'unit'])
    </div></div></div>
@endsection
