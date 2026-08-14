@extends('layouts.adminlte')

@section('title', 'Approval Inbox')
@section('page_title', 'Approval Inbox')
@section('breadcrumb')<li class="breadcrumb-item active">Approval Inbox</li>@endsection

@section('content')
<div class="container-fluid">
    <div class="alert alert-warning">
        <i class="fas fa-lock me-2"></i>{{ $message }}
    </div>
</div>
@endsection
