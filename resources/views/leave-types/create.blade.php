@extends('layouts.adminlte')
@section('title', 'Create Leave Type') @section('page_title', 'Create Leave Type')
@section('breadcrumb')<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('leave-types.index') }}">Leave Types</a></li><li class="breadcrumb-item active">Create</li>@endsection
@section('content')<div class="container-fluid"><div class="card"><div class="card-body">@include('partials.master-data.validation-summary')<form method="POST" action="{{ route('leave-types.store') }}">@csrf @include('leave-types._form', ['submitLabel' => 'Save Leave Type', 'cancelUrl' => route('leave-types.index')])</form></div></div></div>@endsection
