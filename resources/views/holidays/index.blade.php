@extends('layouts.adminlte')
@section('title', 'Holiday Master Data')
@section('page_title', 'Holiday Master Data')
@section('breadcrumb')<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item active">Holidays</li>@endsection
@section('content')
<div class="container-fluid"><div class="card"><div class="card-header d-flex justify-content-end"><a href="{{ route('holidays.create') }}" class="btn btn-primary btn-sm">New Holiday</a></div><div class="card-body">
@include('partials.master-data.search-toolbar', ['routePrefix' => 'holidays'])
<div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Date</th><th>Name</th><th>Type</th><th>Status</th><th class="text-end">Actions</th></tr></thead><tbody>
@forelse($holidays as $holiday)<tr><td>{{ $holiday->holiday_date->format('Y-m-d') }}</td><td>{{ $holiday->name }}</td><td>{{ $holiday->holiday_type ? str_replace('_', ' ', ucfirst($holiday->holiday_type)) : '—' }}</td><td>{{ $holiday->is_active ? 'Active' : 'Inactive' }}</td><td class="text-end"><a href="{{ route('holidays.show', $holiday) }}" class="btn btn-info btn-sm">View</a>@if(!$holiday->trashed()) <a href="{{ route('holidays.edit', $holiday) }}" class="btn btn-warning btn-sm">Edit</a><form class="d-inline" method="POST" action="{{ route('holidays.archive', $holiday) }}">@csrf<button class="btn btn-danger btn-sm" onclick="return confirm('Archive this holiday?')">Archive</button></form>@else<form class="d-inline" method="POST" action="{{ route('holidays.restore', $holiday->id) }}">@csrf<button class="btn btn-success btn-sm">Restore</button></form>@endif</td></tr>
@empty<tr><td colspan="5" class="text-center">No holidays found.</td></tr>@endforelse</tbody></table></div>{{ $holidays->links() }}
</div></div></div>
@endsection
