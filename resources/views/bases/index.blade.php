@extends('layouts.adminlte')
@section('title', 'Base Master Data')
@section('page_title', 'Base Master Data')
@section('breadcrumb')<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item active">Base</li>@endsection
@section('content')
    <div class="container-fluid"><div class="card"><div class="card-header d-flex justify-content-between align-items-center"><h3 class="card-title mb-0">Bases</h3><a href="{{ route('bases.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>New Base</a></div><div class="card-body">
        @include('partials.master-data.search-toolbar', ['routePrefix' => 'bases'])
        @include('partials.master-data.index-table', ['records' => $bases, 'routePrefix' => 'bases', 'codeField' => 'base_code', 'nameField' => 'base_name', 'emptyLabel' => 'bases', 'archiveLabel' => 'base'])
    </div></div></div>
@endsection
