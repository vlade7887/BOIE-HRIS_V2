@extends('layouts.adminlte')

@section('title', 'File Leave Draft')
@section('page_title', 'File Leave Draft')
@section('breadcrumb')<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li><li class="breadcrumb-item active">Leave Draft</li>@endsection

@section('content')
<div class="container-fluid">
    <div class="alert alert-info">Slice 3 preview only. This page saves drafts and does not submit Leave for approval or create an approval request.</div>
    <div class="row">
        <div class="col-lg-8">
            <div class="card card-primary"><div class="card-header"><h3 class="card-title">Leave details</h3></div><div class="card-body">
                <p><strong>Requester:</strong> {{ $requester->first_name }} {{ $requester->last_name }} ({{ $requester->employee_no }})</p>
                @if(isset($preview))
                    <div class="alert alert-success"><strong>Preview ready.</strong> {{ number_format($preview['total_units'], 2) }} Leave unit(s) counted.</div>
                    @if(!$preview['allocation']['sufficient'])<div class="alert alert-warning">Available balance is insufficient for this request. No reservation was created.</div>@endif
                    <h5>Calendar snapshot preview</h5><div class="table-responsive"><table class="table table-sm"><thead><tr><th>Date</th><th>Calendar</th><th>Units</th><th>Period</th></tr></thead><tbody>
                    @foreach($preview['calendar']['days'] as $day)<tr><td>{{ $day['date'] }}</td><td>{{ $day['is_working_day'] ? 'Working day' : ($day['holiday_name'] ?: 'Weekend') }}</td><td>{{ number_format($day['counted_units'], 2) }}</td><td>{{ $day['half_day_period'] ?: 'Full day' }}</td></tr>@endforeach
                    </tbody></table></div>
                @endif
                <form method="POST" action="{{ route('leave-requests.preview') }}">@csrf
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">Leave Type</label><select name="leave_type_id" class="form-control" required><option value="">Choose...</option>@foreach($leaveTypes as $type)<option value="{{ $type->id }}" @selected(old('leave_type_id', data_get($preview, 'leave_type.id')) == $type->id)>{{ $type->name }} ({{ $type->code }})</option>@endforeach</select></div>
                        <div class="col-md-3"><label class="form-label">Start Date</label><input type="date" name="start_date" value="{{ old('start_date', data_get($preview, 'start_date')?->toDateString()) }}" class="form-control" required></div>
                        <div class="col-md-3"><label class="form-label">End Date</label><input type="date" name="end_date" value="{{ old('end_date', data_get($preview, 'end_date')?->toDateString()) }}" class="form-control" required></div>
                        <div class="col-md-3"><label class="form-label">Unit</label><select name="requested_unit" class="form-control"><option value="1" @selected(old('requested_unit', data_get($preview, 'requested_unit', 1)) == 1)>Full day</option><option value="0.5" @selected(old('requested_unit', data_get($preview, 'requested_unit', 1)) == 0.5)>Half day</option></select></div>
                        <div class="col-md-3"><label class="form-label">Half-day period</label><select name="half_day_period" class="form-control"><option value="">Not applicable</option><option value="AM" @selected(old('half_day_period', data_get($preview, 'half_day_period', '')) === 'AM')>AM</option><option value="PM" @selected(old('half_day_period', data_get($preview, 'half_day_period', '')) === 'PM')>PM</option></select></div>
                        <div class="col-md-3"><label class="form-label">Return-to-work date</label><input type="date" name="returned_to_work_date" value="{{ old('returned_to_work_date', data_get($preview, 'returned_to_work_date', '')) }}" class="form-control"><small class="text-muted">Required for Sick Leave.</small></div>
                        <div class="col-12"><label class="form-label">Reason</label><textarea name="reason" class="form-control" rows="3">{{ old('reason') }}</textarea></div>
                    </div>
                    <div class="mt-4"><button class="btn btn-primary">Preview Leave</button><button formaction="{{ route('leave-requests.drafts.store') }}" class="btn btn-outline-secondary">Save Draft</button></div>
                </form>
            </div></div>
        </div>
    </div>
</div>
@endsection
