@extends('layouts.adminlte')
@section('title', 'Company Master Data')
@section('page_title', 'Company Master Data')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Company</li>
@endsection
@section('content')
    <div class="container-fluid"><div class="card">
        <div class="card-header d-flex justify-content-end align-items-center">
            <a href="{{ route('companies.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>New Company</a>
        </div>
        <div class="card-body">
            @include('partials.master-data.search-toolbar', ['routePrefix' => 'companies'])
            @include('partials.master-data.index-table', ['records' => $companies, 'routePrefix' => 'companies', 'codeField' => 'company_code', 'nameField' => 'company_name', 'emptyLabel' => 'companies', 'archiveLabel' => 'company'])
        </div>
    </div></div>
@endsection
