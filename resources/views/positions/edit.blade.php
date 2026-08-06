@extends('layouts.adminlte')
@section('title', 'Edit Position') @section('page_title', 'Edit Position')
@section('breadcrumb')<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('positions.index') }}">Position</a></li><li class="breadcrumb-item active">Edit</li>@endsection
@section('content')<div class="container-fluid"><div class="card"><div class="card-header"><h3 class="card-title">Position Details</h3></div><div class="card-body">
@include('partials.master-data.validation-summary')<form action="{{ route('positions.update', $position) }}" method="POST">@csrf @method('PUT')<div class="row g-3">
@include('partials.master-data.code-name-fields', ['model' => $position, 'codeField' => 'position_code', 'codeLabel' => 'Position Code', 'nameField' => 'position_name', 'nameLabel' => 'Position Name'])
@include('partials.master-data.status-field', ['value' => $position->is_active ? '1' : '0']) @include('partials.master-data.remarks-field', ['value' => $position->remarks])
</div>@include('partials.master-data.form-actions', ['submitLabel' => 'Update Position', 'cancelUrl' => route('positions.show', $position)])</form></div></div></div>@endsection
