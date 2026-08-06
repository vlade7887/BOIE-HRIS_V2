@extends('layouts.adminlte')
@section('title', 'Create Unit') @section('page_title', 'Create Unit')
@section('breadcrumb')<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('units.index') }}">Unit</a></li><li class="breadcrumb-item active">Create</li>@endsection
@section('content')<div class="container-fluid"><div class="card"><div class="card-header"><h3 class="card-title">Unit Details</h3></div><div class="card-body">
    @include('partials.master-data.validation-summary')
    <form action="{{ route('units.store') }}" method="POST">@csrf<div class="row g-3">
        @include('partials.master-data.code-name-fields', ['model' => $unit, 'codeField' => 'unit_code', 'codeLabel' => 'Unit Code', 'nameField' => 'unit_name', 'nameLabel' => 'Unit Name'])
        @include('partials.master-data.status-field', ['value' => $unit->is_active ?? true])
        @include('partials.master-data.remarks-field', ['value' => $unit->remarks])
    </div>@include('partials.master-data.form-actions', ['submitLabel' => 'Save Unit', 'cancelUrl' => route('units.index')])</form>
</div></div></div>@endsection
