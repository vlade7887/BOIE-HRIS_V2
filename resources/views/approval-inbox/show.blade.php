@extends('layouts.adminlte')

@section('title', 'Approval Request #'.$request->id)
@section('page_title', 'Approval Request #'.$request->id)
@section('breadcrumb')<li class="breadcrumb-item"><a href="{{ route('approval-inbox.index') }}">Approval Inbox</a></li><li class="breadcrumb-item active">Request #{{ $request->id }}</li>@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8">
            <div class="card card-primary">
                <div class="card-header"><h3 class="card-title">Request information</h3></div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4">Request ID</dt><dd class="col-sm-8">#{{ $request->id }}</dd>
                        <dt class="col-sm-4">Module</dt><dd class="col-sm-8">{{ $request->module_key }}</dd>
                        <dt class="col-sm-4">Requester</dt><dd class="col-sm-8">{{ $request->requester->first_name }} {{ $request->requester->last_name }}</dd>
                        <dt class="col-sm-4">Status</dt><dd class="col-sm-8"><span class="badge badge-{{ $request->status === 'pending' ? 'warning' : ($request->status === 'approved' ? 'success' : 'secondary') }}">{{ ucfirst($request->status) }}</span></dd>
                        <dt class="col-sm-4">Workflow snapshot</dt><dd class="col-sm-8">{{ $request->workflow_name ?? '—' }} · {{ $request->workflow_code }} v{{ $request->workflow_version }}</dd>
                        <dt class="col-sm-4">Department snapshot</dt><dd class="col-sm-8">{{ $request->requestDepartment?->department_name ?? 'None' }}</dd>
                        <dt class="col-sm-4">Submitted at</dt><dd class="col-sm-8">{{ $request->submitted_at?->format('Y-m-d H:i:s') ?? '—' }}</dd>
                        <dt class="col-sm-4">Completed at</dt><dd class="col-sm-8">{{ $request->completed_at?->format('Y-m-d H:i:s') ?? '—' }}</dd>
                    </dl>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h3 class="card-title">Approval route</h3></div>
                <div class="card-body p-0"><div class="table-responsive"><table class="table table-bordered mb-0"><thead><tr><th>Step</th><th>Canonical approver</th><th>Type</th><th>Status</th><th>Activated</th><th>Acted</th></tr></thead><tbody>
                    @foreach($request->steps->sortBy('step_order') as $step)
                        <tr><td>{{ $step->step_order }}</td><td>{{ $step->canonicalApprover->first_name }} {{ $step->canonicalApprover->last_name }}</td><td>{{ $step->step_type === 'hr_final' ? 'HR Final Approval' : 'Employee-selected approver' }}</td><td>{{ ucfirst($step->status) }}</td><td>{{ $step->activated_at?->format('Y-m-d H:i:s') ?? '—' }}</td><td>{{ $step->acted_at?->format('Y-m-d H:i:s') ?? '—' }}</td></tr>
                    @endforeach
                </tbody></table></div></div>
            </div>

            <div class="card">
                <div class="card-header"><h3 class="card-title">Action history</h3></div>
                <div class="card-body p-0"><div class="table-responsive"><table class="table table-bordered mb-0"><thead><tr><th>Action</th><th>Actor</th><th>Canonical approver</th><th>Acting for</th><th>Remarks</th><th>Acted at</th></tr></thead><tbody>
                    @forelse($request->actions->sortBy('acted_at') as $action)
                        <tr><td>{{ ucfirst($action->action) }}</td><td>{{ $action->actorEmployee ? $action->actorEmployee->first_name.' '.$action->actorEmployee->last_name : 'System' }}</td><td>{{ $action->canonicalApprover ? $action->canonicalApprover->first_name.' '.$action->canonicalApprover->last_name : '—' }}</td><td>{{ $action->actingFor ? $action->actingFor->first_name.' '.$action->actingFor->last_name : '—' }}</td><td>{{ $action->remarks ?? '—' }}</td><td>{{ $action->acted_at?->format('Y-m-d H:i:s') }}</td></tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted">No actions recorded.</td></tr>
                    @endforelse
                </tbody></table></div></div>
            </div>
        </div>

        <div class="col-lg-4">
            @if($authorized && $currentStep)
                <div class="card card-warning">
                    <div class="card-header"><h3 class="card-title">Current approval context</h3></div>
                    <div class="card-body">
                        <p><strong>Current canonical approver:</strong><br>{{ $currentStep->canonicalApprover->first_name }} {{ $currentStep->canonicalApprover->last_name }}</p>
                        <p><strong>Logged-in employee:</strong><br>{{ $employee->first_name }} {{ $employee->last_name }}</p>
                        @if($delegation)<p class="alert alert-info mb-3">You are acting for {{ $currentStep->canonicalApprover->first_name }} {{ $currentStep->canonicalApprover->last_name }}.<br><small>Scope: {{ $delegation->scope_type === 'department' ? 'Specific Department' : 'All Approvals' }}</small></p>@else<p class="text-success">Direct Approval</p>@endif
                        <form method="POST" action="{{ route('approval-inbox.approve', $request) }}" class="mb-3">@csrf<input type="hidden" name="idempotency_key" value="{{ $actionKey }}"><label for="approve-remarks">Optional remarks</label><textarea id="approve-remarks" name="remarks" class="form-control mb-2" rows="3"></textarea><button class="btn btn-success w-100" type="submit">Approve</button></form>
                        <form method="POST" action="{{ route('approval-inbox.reject', $request) }}">@csrf<input type="hidden" name="idempotency_key" value="{{ $actionKey }}"><label for="reject-remarks">Rejection reason</label><textarea id="reject-remarks" name="remarks" class="form-control mb-2" rows="3" required></textarea><button class="btn btn-danger w-100" type="submit">Reject</button></form>
                    </div>
                </div>
            @endif

            @if($canCancel)
                <div class="card card-secondary"><div class="card-header"><h3 class="card-title">Requester actions</h3></div><div class="card-body"><p>You may cancel this request while it is {{ $request->status }}.</p><form method="POST" action="{{ route('approval-requests.cancel', $request) }}" onsubmit="return confirm('Cancel this request? This cannot be undone.');">@csrf<input type="hidden" name="idempotency_key" value="{{ $cancelKey }}"><label for="cancel-remarks">Optional remarks</label><textarea id="cancel-remarks" name="remarks" class="form-control mb-2" rows="3"></textarea><button class="btn btn-outline-danger" type="submit">Cancel Request</button></form></div></div>
            @endif
            <a class="btn btn-secondary" href="{{ route('approval-inbox.index') }}">Back to Approval Inbox</a>
        </div>
    </div>
</div>
@endsection
