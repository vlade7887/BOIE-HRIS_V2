@extends('layouts.adminlte')
@section('title', 'Employee User Mapping')
@section('page_title', 'Employee User Mapping')
@section('content')
<div class="container-fluid"><div class="card"><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0">
<thead><tr><th>Employee</th><th>Employee No.</th><th>Mapped User</th><th class="text-end">Action</th></tr></thead><tbody>
@forelse($employees as $employee)
<tr><td>{{ $employee->last_name }}, {{ $employee->first_name }}</td><td>{{ $employee->employee_no }}</td><td>{{ $employee->user?->email ?? 'Not mapped' }}</td><td class="text-end"><a href="{{ route('employee-user-mappings.edit', $employee) }}" class="btn btn-sm btn-primary">Manage</a></td></tr>
@empty<tr><td colspan="4" class="text-center p-4">No employees found.</td></tr>@endforelse
</tbody></table></div></div></div></div>
@endsection
