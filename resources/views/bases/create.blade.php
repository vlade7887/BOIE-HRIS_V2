@extends('layouts.adminlte')

@section('title', 'Create Base')
@section('page_title', 'Create Base')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('bases.index') }}">Base</a></li>
    <li class="breadcrumb-item active">Create</li>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Base Details</h3></div>
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <strong>Please fix the following errors:</strong>
                        <ul class="mb-0 mt-2">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif
                <form action="{{ route('bases.store') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="base_code" class="form-label">Base Code</label>
                            <input type="text" class="form-control @error('base_code') is-invalid @enderror" id="base_code" name="base_code" value="{{ old('base_code', $base->base_code) }}" required>
                            @error('base_code')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="base_name" class="form-label">Base Name</label>
                            <input type="text" class="form-control @error('base_name') is-invalid @enderror" id="base_name" name="base_name" value="{{ old('base_name', $base->base_name) }}" required>
                            @error('base_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="is_active" class="form-label">Status</label>
                            <select class="form-select @error('is_active') is-invalid @enderror" id="is_active" name="is_active">
                                <option value="1" {{ old('is_active', $base->is_active ?? true) == '1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ old('is_active', $base->is_active ?? true) == '0' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('is_active')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-12">
                            <label for="remarks" class="form-label">Remarks</label>
                            <textarea class="form-control @error('remarks') is-invalid @enderror" id="remarks" name="remarks" rows="3">{{ old('remarks', $base->remarks) }}</textarea>
                            @error('remarks')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="mt-4 d-flex gap-2"><button type="submit" class="btn btn-primary">Save Base</button><a href="{{ route('bases.index') }}" class="btn btn-secondary">Cancel</a></div>
                </form>
            </div>
        </div>
    </div>
@endsection
