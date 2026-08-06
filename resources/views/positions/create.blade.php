@extends('layouts.adminlte')
@section('title', 'Create Position')
@section('page_title', 'Create Position')
@section('breadcrumb')<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('positions.index') }}">Position</a></li><li class="breadcrumb-item active">Create</li>@endsection
@section('content')
<div class="container-fluid"><div class="card"><div class="card-header"><h3 class="card-title">Position Details</h3></div><div class="card-body">
    @if ($errors->any())<div class="alert alert-danger"><strong>Please fix the following errors:</strong><ul class="mb-0 mt-2">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <form action="{{ route('positions.store') }}" method="POST">@csrf<div class="row g-3">
        <div class="col-md-6"><label for="position_code" class="form-label">Position Code</label><input type="text" class="form-control @error('position_code') is-invalid @enderror" id="position_code" name="position_code" value="{{ old('position_code', $position->position_code) }}" required>@error('position_code')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
        <div class="col-md-6"><label for="position_name" class="form-label">Position Name</label><input type="text" class="form-control @error('position_name') is-invalid @enderror" id="position_name" name="position_name" value="{{ old('position_name', $position->position_name) }}" required>@error('position_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
        <div class="col-md-6"><label for="is_active" class="form-label">Status</label><select class="form-select @error('is_active') is-invalid @enderror" id="is_active" name="is_active"><option value="1" {{ old('is_active', $position->is_active ?? true) == '1' ? 'selected' : '' }}>Active</option><option value="0" {{ old('is_active', $position->is_active ?? true) == '0' ? 'selected' : '' }}>Inactive</option></select>@error('is_active')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
        <div class="col-md-12"><label for="remarks" class="form-label">Remarks</label><textarea class="form-control @error('remarks') is-invalid @enderror" id="remarks" name="remarks" rows="3">{{ old('remarks', $position->remarks) }}</textarea>@error('remarks')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
    </div><div class="mt-4 d-flex gap-2"><button type="submit" class="btn btn-primary">Save Position</button><a href="{{ route('positions.index') }}" class="btn btn-secondary">Cancel</a></div></form>
</div></div></div>
@endsection
