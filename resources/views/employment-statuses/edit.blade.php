@extends('layouts.adminlte')
@section('title', 'Edit Employment Status') @section('page_title', 'Edit Employment Status')
@section('breadcrumb')<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('employment-statuses.index') }}">Employment Status</a></li><li class="breadcrumb-item active">Edit</li>@endsection
@section('content')<div class="container-fluid"><div class="card"><div class="card-header"><h3 class="card-title">Edit Employment Status</h3></div><div class="card-body">
@include('partials.master-data.validation-summary')<form action="{{ route('employment-statuses.update', $employmentStatus) }}" method="POST">@csrf @method('PUT')<div class="row g-3">
@include('partials.master-data.code-name-fields', ['model' => $employmentStatus, 'codeField' => 'status_code', 'codeLabel' => 'Status Code', 'nameField' => 'status_name', 'nameLabel' => 'Status Name'])
@include('partials.master-data.status-field', ['value' => $employmentStatus->is_active ? '1' : '0']) @include('partials.master-data.remarks-field', ['value' => $employmentStatus->remarks])
</div>@include('partials.master-data.form-actions', ['submitLabel' => 'Update Employment Status', 'cancelUrl' => route('employment-statuses.show', $employmentStatus)])</form></div></div></div>@endsection
