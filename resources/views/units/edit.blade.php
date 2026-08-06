@extends('layouts.adminlte')
@section('title', 'Edit Unit') @section('page_title', 'Edit Unit')
@section('breadcrumb')<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('units.index') }}">Unit</a></li><li class="breadcrumb-item active">Edit</li>@endsection
@section('content')<div class="container-fluid"><div class="card"><div class="card-header"><h3 class="card-title">Edit Unit</h3></div><div class="card-body">
    @include('partials.master-data.validation-summary')
    <form action="{{ route('units.update', $unit) }}" method="POST">@csrf @method('PUT')<div class="row g-3">
        @include('partials.master-data.code-name-fields', ['model' => $unit, 'codeField' => 'unit_code', 'codeLabel' => 'Unit Code', 'nameField' => 'unit_name', 'nameLabel' => 'Unit Name'])
        @include('partials.master-data.status-field', ['value' => $unit->is_active ? '1' : '0'])
        @include('partials.master-data.remarks-field', ['value' => $unit->remarks])
    </div>@include('partials.master-data.form-actions', ['submitLabel' => 'Update Unit', 'cancelUrl' => route('units.show', $unit)])</form>
</div></div></div>@endsection
