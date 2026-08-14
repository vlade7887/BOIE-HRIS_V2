@extends('layouts.adminlte')

@section('title', 'Approval Inbox')
@section('page_title', 'Approval Inbox')
@section('breadcrumb')<li class="breadcrumb-item active">Approval Inbox</li>@endsection

@section('content')
<div class="container-fluid">
    <div class="card card-primary">
        <div class="card-header"><h3 class="card-title">Requests requiring your action</h3></div>
        <div class="card-body">
            <p class="text-muted">Signed in as {{ $employee->first_name }} {{ $employee->last_name }} ({{ $employee->employee_no }}).</p>
            @if($items->isEmpty())
                <p class="mb-0 text-muted">There are no pending approval requests assigned to you.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead><tr><th>Request</th><th>Module</th><th>Requester</th><th>Department snapshot</th><th>Workflow snapshot</th><th>Current canonical approver</th><th>Submitted</th><th>Status</th><th></th></tr></thead>
                        <tbody>
                        @foreach($items as $item)
                            @php($request = $item['request'])
                            @php($step = $item['currentStep'])
                            <tr>
                                <td>#{{ $request->id }}</td>
                                <td>{{ $request->module_key }}</td>
                                <td>{{ $request->requester->first_name }} {{ $request->requester->last_name }}</td>
                                <td>{{ $request->requestDepartment?->department_name ?? 'None' }}</td>
                                <td>{{ $request->workflow_name ?? '—' }} <small class="text-muted">({{ $request->workflow_code }} v{{ $request->workflow_version }})</small></td>
                                <td>
                                    {{ $step?->canonicalApprover?->first_name }} {{ $step?->canonicalApprover?->last_name }}
                                    @if($item['delegation'])<br><span class="badge badge-info">Acting for: {{ $step->canonicalApprover->first_name }} {{ $step->canonicalApprover->last_name }}</span>@endif
                                </td>
                                <td>{{ $request->submitted_at?->format('Y-m-d H:i') }}</td>
                                <td><span class="badge badge-warning">{{ ucfirst($request->status) }}</span></td>
                                <td><a class="btn btn-sm btn-primary" href="{{ route('approval-inbox.show', $request) }}">View</a></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
