@extends('layouts.adminlte')
@section('title', 'Edit Holiday') @section('page_title', 'Edit Holiday')
@section('breadcrumb')<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('holidays.index') }}">Holidays</a></li><li class="breadcrumb-item active">Edit</li>@endsection
@section('content')<div class="container-fluid"><div class="card"><div class="card-body">@include('partials.master-data.validation-summary')<form method="POST" action="{{ route('holidays.update', $holiday) }}">@csrf @method('PUT') @include('holidays._form', ['submitLabel' => 'Update Holiday', 'cancelUrl' => route('holidays.show', $holiday)])</form></div></div></div>@endsection
