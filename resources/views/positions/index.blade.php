@extends('layouts.adminlte')
@section('title', 'Position Master Data')
@section('page_title', 'Position Master Data')
@section('breadcrumb')<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item active">Position</li>@endsection
@section('content')
    <div class="container-fluid"><div class="card"><div class="card-header d-flex justify-content-between align-items-center"><h3 class="card-title mb-0">Positions</h3><a href="{{ route('positions.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>New Position</a></div><div class="card-body">
        @include('partials.master-data.search-toolbar', ['routePrefix' => 'positions'])
        @include('partials.master-data.index-table', ['records' => $positions, 'routePrefix' => 'positions', 'codeField' => 'position_code', 'nameField' => 'position_name', 'emptyLabel' => 'positions', 'archiveLabel' => 'position'])
    </div></div></div>
@endsection
