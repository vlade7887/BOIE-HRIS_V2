@extends('layouts.adminlte')

@section('title', 'Create Unit')
@section('page_title', 'Create Unit')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('units.index') }}">Unit</a></li>
    <li class="breadcrumb-item active">Create</li>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Unit Details</h3></div>
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger"><strong>Please fix the following errors:</strong><ul class="mb-0 mt-2">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                @endif
                <form action="{{ route('units.store') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6"><label for="unit_code" class="form-label">Unit Code</label><input type="text" class="form-control @error('unit_code') is-invalid @enderror" id="unit_code" name="unit_code" value="{{ old('unit_code', $unit->unit_code) }}" required>@error('unit_code')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
                        <div class="col-md-6"><label for="unit_name" class="form-label">Unit Name</label><input type="text" class="form-control @error('unit_name') is-invalid @enderror" id="unit_name" name="unit_name" value="{{ old('unit_name', $unit->unit_name) }}" required>@error('unit_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
                        <div class="col-md-6"><label for="is_active" class="form-label">Status</label><select class="form-select @error('is_active') is-invalid @enderror" id="is_active" name="is_active"><option value="1" {{ old('is_active', $unit->is_active ?? true) == '1' ? 'selected' : '' }}>Active</option><option value="0" {{ old('is_active', $unit->is_active ?? true) == '0' ? 'selected' : '' }}>Inactive</option></select>@error('is_active')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
                        <div class="col-md-12"><label for="remarks" class="form-label">Remarks</label><textarea class="form-control @error('remarks') is-invalid @enderror" id="remarks" name="remarks" rows="3">{{ old('remarks', $unit->remarks) }}</textarea>@error('remarks')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
                    </div>
                    <div class="mt-4 d-flex gap-2"><button type="submit" class="btn btn-primary">Save Unit</button><a href="{{ route('units.index') }}" class="btn btn-secondary">Cancel</a></div>
                </form>
            </div>
        </div>
    </div>
@endsection
