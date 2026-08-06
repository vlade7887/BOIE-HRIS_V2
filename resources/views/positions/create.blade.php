@extends('layouts.adminlte')
@section('title', 'Create Position') @section('page_title', 'Create Position')
@section('breadcrumb')<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('positions.index') }}">Position</a></li><li class="breadcrumb-item active">Create</li>@endsection
@section('content')<div class="container-fluid"><div class="card"><div class="card-header"><h3 class="card-title">Position Details</h3></div><div class="card-body">
@include('partials.master-data.validation-summary')<form action="{{ route('positions.store') }}" method="POST">@csrf<div class="row g-3">
@include('partials.master-data.code-name-fields', ['model' => $position, 'codeField' => 'position_code', 'codeLabel' => 'Position Code', 'nameField' => 'position_name', 'nameLabel' => 'Position Name'])
@include('partials.master-data.status-field', ['value' => $position->is_active ?? true]) @include('partials.master-data.remarks-field', ['value' => $position->remarks])
</div>@include('partials.master-data.form-actions', ['submitLabel' => 'Save Position', 'cancelUrl' => route('positions.index')])</form></div></div></div>@endsection
