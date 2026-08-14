@extends('layouts.adminlte')

@section('title', 'Approval Demo Unavailable')
@section('page_title', 'Approval Request Demo')
@section('breadcrumb')<li class="breadcrumb-item active">Approval Demo</li>@endsection

@section('content')
<div class="container-fluid">
    <div class="alert alert-warning" role="alert">
        <h5><i class="fas fa-exclamation-triangle mr-2"></i>Approval Demo unavailable</h5>
        <p class="mb-0">{{ $message }}</p>
    </div>
</div>
@endsection
