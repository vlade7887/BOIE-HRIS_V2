@extends('layouts.adminlte')
@section('title', 'Edit Company') @section('page_title', 'Edit Company')
@section('breadcrumb')<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('companies.index') }}">Company</a></li><li class="breadcrumb-item active">Edit</li>@endsection
@section('content')<div class="container-fluid"><div class="card"><div class="card-header"><h3 class="card-title">Edit Company</h3></div><div class="card-body">
@include('partials.master-data.validation-summary')<form action="{{ route('companies.update', $company) }}" method="POST">@csrf @method('PUT')<div class="row g-3">
@include('partials.master-data.code-name-fields', ['model' => $company, 'codeField' => 'company_code', 'codeLabel' => 'Company Code', 'nameField' => 'company_name', 'nameLabel' => 'Company Name'])
@include('partials.master-data.field', ['name' => 'contact_person', 'label' => 'Contact Person', 'value' => $company->contact_person])
@include('partials.master-data.field', ['name' => 'contact_number', 'label' => 'Contact Number', 'value' => $company->contact_number])
@include('partials.master-data.field', ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'value' => $company->email])
@include('partials.master-data.status-field', ['value' => $company->is_active ? '1' : '0'])
<div class="col-md-12"><label for="address" class="form-label">Address</label><textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="3">{{ old('address', $company->address) }}</textarea>@error('address')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
@include('partials.master-data.remarks-field', ['value' => $company->remarks])
</div>@include('partials.master-data.form-actions', ['submitLabel' => 'Update Company', 'cancelUrl' => route('companies.show', $company)])</form></div></div></div>@endsection
