@extends('layouts.adminlte')
@section('title', 'Leave Type Master Data')
@section('page_title', 'Leave Type Master Data')
@section('breadcrumb')<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item active">Leave Types</li>@endsection
@section('content')
<div class="container-fluid"><div class="card"><div class="card-header d-flex justify-content-end"><a href="{{ route('leave-types.create') }}" class="btn btn-primary btn-sm">New Leave Type</a></div><div class="card-body">
@include('partials.master-data.search-toolbar', ['routePrefix' => 'leave-types'])
<div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Code</th><th>Name</th><th>Annual Entitlement</th><th>Half Day</th><th>Status</th><th class="text-end">Actions</th></tr></thead><tbody>
@forelse($leaveTypes as $leaveType)<tr><td>{{ $leaveType->code }}</td><td>{{ $leaveType->name }}</td><td>{{ number_format((float) $leaveType->annual_entitlement_days, 2) }} days</td><td>{{ $leaveType->allows_half_day ? 'Yes' : 'No' }}</td><td>{{ $leaveType->is_active ? 'Active' : 'Inactive' }}</td><td class="text-end"><a href="{{ route('leave-types.show', $leaveType) }}" class="btn btn-info btn-sm">View</a>@if(!$leaveType->trashed()) <a href="{{ route('leave-types.edit', $leaveType) }}" class="btn btn-warning btn-sm">Edit</a><form class="d-inline" method="POST" action="{{ route('leave-types.archive', $leaveType) }}">@csrf<button class="btn btn-danger btn-sm" onclick="return confirm('Archive this leave type?')">Archive</button></form>@else<form class="d-inline" method="POST" action="{{ route('leave-types.restore', $leaveType->id) }}">@csrf<button class="btn btn-success btn-sm">Restore</button></form>@endif</td></tr>
@empty<tr><td colspan="6" class="text-center">No leave types found.</td></tr>@endforelse</tbody></table></div>{{ $leaveTypes->links() }}
</div></div></div>
@endsection
