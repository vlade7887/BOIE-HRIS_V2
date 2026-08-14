@extends('layouts.adminlte')
@section('title', 'Edit Leave Type') @section('page_title', 'Edit Leave Type')
@section('breadcrumb')<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('leave-types.index') }}">Leave Types</a></li><li class="breadcrumb-item active">Edit</li>@endsection
@section('content')<div class="container-fluid"><div class="card"><div class="card-body">@include('partials.master-data.validation-summary')<form method="POST" action="{{ route('leave-types.update', $leaveType) }}">@csrf @method('PUT') @include('leave-types._form', ['submitLabel' => 'Update Leave Type', 'cancelUrl' => route('leave-types.show', $leaveType)])</form></div></div></div>@endsection
