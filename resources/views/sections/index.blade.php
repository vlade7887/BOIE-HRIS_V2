@extends('layouts.adminlte')
@section('title', 'Section Master Data')
@section('page_title', 'Section Master Data')
@section('breadcrumb')<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item active">Section</li>@endsection
@section('content')
    <div class="container-fluid"><div class="card"><div class="card-header d-flex justify-content-between align-items-center"><h3 class="card-title mb-0">Sections</h3><a href="{{ route('sections.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>New Section</a></div><div class="card-body">
        @include('partials.master-data.search-toolbar', ['routePrefix' => 'sections'])
        @include('partials.master-data.index-table', ['records' => $sections, 'routePrefix' => 'sections', 'codeField' => 'section_code', 'nameField' => 'section_name', 'emptyLabel' => 'sections', 'archiveLabel' => 'section'])
    </div></div></div>
@endsection
