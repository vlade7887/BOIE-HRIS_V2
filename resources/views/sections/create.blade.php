@extends('layouts.adminlte')
@section('title', 'Create Section') @section('page_title', 'Create Section')
@section('breadcrumb')<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('sections.index') }}">Section</a></li><li class="breadcrumb-item active">Create</li>@endsection
@section('content')<div class="container-fluid"><div class="card"><div class="card-header"><h3 class="card-title">Section Details</h3></div><div class="card-body">
@include('partials.master-data.validation-summary')<form action="{{ route('sections.store') }}" method="POST">@csrf<div class="row g-3">
@include('partials.master-data.code-name-fields', ['model' => $section, 'codeField' => 'section_code', 'codeLabel' => 'Section Code', 'nameField' => 'section_name', 'nameLabel' => 'Section Name'])
@include('partials.master-data.status-field', ['value' => $section->is_active ?? true]) @include('partials.master-data.remarks-field', ['value' => $section->remarks])
</div>@include('partials.master-data.form-actions', ['submitLabel' => 'Save Section', 'cancelUrl' => route('sections.index')])</form></div></div></div>@endsection
