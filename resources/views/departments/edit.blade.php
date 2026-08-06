@extends('layouts.adminlte')
@section('title', 'Edit Department') @section('page_title', 'Edit Department')
@section('breadcrumb')<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('departments.index') }}">Department</a></li><li class="breadcrumb-item active">Edit</li>@endsection
@section('content')<div class="container-fluid"><div class="card"><div class="card-header"><h3 class="card-title">Edit Department</h3></div><div class="card-body">
@include('partials.master-data.validation-summary')<form action="{{ route('departments.update', $department) }}" method="POST">@csrf @method('PUT')<div class="row g-3">
@include('partials.master-data.code-name-fields', ['model' => $department, 'codeField' => 'department_code', 'codeLabel' => 'Department Code', 'nameField' => 'department_name', 'nameLabel' => 'Department Name'])
<div class="col-md-6"><label for="unit_id" class="form-label">Unit <span class="text-muted">(Optional)</span></label><select class="form-select @error('unit_id') is-invalid @enderror" id="unit_id" name="unit_id"><option value="">No Unit</option>@foreach($units as $unit)<option value="{{ $unit->id }}" {{ old('unit_id', $department->unit_id) == $unit->id ? 'selected' : '' }}>{{ $unit->unit_name }}</option>@endforeach</select>@error('unit_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
@include('partials.master-data.status-field', ['value' => $department->is_active ? '1' : '0']) @include('partials.master-data.remarks-field', ['value' => $department->remarks])
</div>@include('partials.master-data.form-actions', ['submitLabel' => 'Update Department', 'cancelUrl' => route('departments.show', $department)])</form></div></div></div>@endsection
