@extends('layouts.adminlte')
@section('title', 'Edit Section') @section('page_title', 'Edit Section')
@section('breadcrumb')<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('sections.index') }}">Section</a></li><li class="breadcrumb-item active">Edit</li>@endsection
@section('content')<div class="container-fluid"><div class="card"><div class="card-header"><h3 class="card-title">Edit Section</h3></div><div class="card-body">
@include('partials.master-data.validation-summary')<form action="{{ route('sections.update', $section) }}" method="POST">@csrf @method('PUT')<div class="row g-3">
@include('partials.master-data.code-name-fields', ['model' => $section, 'codeField' => 'section_code', 'codeLabel' => 'Section Code', 'nameField' => 'section_name', 'nameLabel' => 'Section Name'])
@include('partials.master-data.status-field', ['value' => $section->is_active ? '1' : '0']) @include('partials.master-data.remarks-field', ['value' => $section->remarks])
</div>@include('partials.master-data.form-actions', ['submitLabel' => 'Update Section', 'cancelUrl' => route('sections.show', $section)])</form></div></div></div>@endsection
