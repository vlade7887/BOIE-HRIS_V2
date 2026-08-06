@extends('layouts.adminlte')
@section('title', 'Create Base') @section('page_title', 'Create Base')
@section('breadcrumb')<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('bases.index') }}">Base</a></li><li class="breadcrumb-item active">Create</li>@endsection
@section('content')<div class="container-fluid"><div class="card"><div class="card-header"><h3 class="card-title">Base Details</h3></div><div class="card-body">
    @include('partials.master-data.validation-summary')
    <form action="{{ route('bases.store') }}" method="POST">@csrf<div class="row g-3">
        @include('partials.master-data.code-name-fields', ['model' => $base, 'codeField' => 'base_code', 'codeLabel' => 'Base Code', 'nameField' => 'base_name', 'nameLabel' => 'Base Name'])
        @include('partials.master-data.status-field', ['value' => $base->is_active ?? true])
        @include('partials.master-data.remarks-field', ['value' => $base->remarks])
    </div>@include('partials.master-data.form-actions', ['submitLabel' => 'Save Base', 'cancelUrl' => route('bases.index')])</form>
</div></div></div>@endsection
