@extends('layouts.adminlte')
@section('title', 'Edit Employee Class') @section('page_title', 'Edit Employee Class')
@section('breadcrumb')<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item"><a href="{{ route('employee-classes.index') }}">Employee Class</a></li><li class="breadcrumb-item active">Edit</li>@endsection
@section('content')<div class="container-fluid"><div class="card"><div class="card-header"><h3 class="card-title">Edit Employee Class</h3></div><div class="card-body">
@include('partials.master-data.validation-summary')<form action="{{ route('employee-classes.update', $employeeClass) }}" method="POST">@csrf @method('PUT')<div class="row g-3">
@include('partials.master-data.code-name-fields', ['model' => $employeeClass, 'codeField' => 'class_code', 'codeLabel' => 'Class Code', 'nameField' => 'class_name', 'nameLabel' => 'Class Name'])
@include('partials.master-data.status-field', ['value' => $employeeClass->is_active ? '1' : '0']) @include('partials.master-data.remarks-field', ['value' => $employeeClass->remarks])
</div>@include('partials.master-data.form-actions', ['submitLabel' => 'Update Employee Class', 'cancelUrl' => route('employee-classes.show', $employeeClass)])</form></div></div></div>@endsection
