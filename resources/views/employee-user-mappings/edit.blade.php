@extends('layouts.adminlte')
@section('title', 'Map Employee User')
@section('page_title', 'Map Employee User')
@section('content')
<div class="container-fluid"><div class="card"><div class="card-body"><form method="POST" action="{{ route('employee-user-mappings.update', $employee) }}">@csrf @method('PUT')
<p><strong>{{ $employee->last_name }}, {{ $employee->first_name }}</strong> ({{ $employee->employee_no }})</p>
<div class="mb-3"><label for="user_id" class="form-label">System User</label><select name="user_id" id="user_id" class="form-select @error('user_id') is-invalid @enderror"><option value="">Not mapped</option>@foreach($users as $user)<option value="{{ $user->id }}" @selected(old('user_id', $employee->user_id) == $user->id)>{{ $user->name }} ({{ $user->email }})</option>@endforeach</select>@error('user_id')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
<button class="btn btn-primary">Save Mapping</button> <a href="{{ route('employee-user-mappings.index') }}" class="btn btn-secondary">Cancel</a>
</form></div></div></div>
@endsection
