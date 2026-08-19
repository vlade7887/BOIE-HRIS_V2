@extends('layouts.adminlte')
@section('title', 'Create Holiday') @section('page_title', 'Create Holiday')
@section('breadcrumb')<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('holidays.index') }}">Holidays</a></li><li class="breadcrumb-item active">Create</li>@endsection
@section('content')<div class="container-fluid"><div class="card"><div class="card-body">@include('partials.master-data.validation-summary')<form method="POST" action="{{ route('holidays.store') }}">@csrf @include('holidays._form', ['submitLabel' => 'Save Holiday', 'cancelUrl' => route('holidays.index')])</form></div></div></div>@endsection
