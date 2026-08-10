@extends('layouts.adminlte')

@section('title', 'Audit Record')
@section('page_title', 'Audit Record')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <dl class="row">
                <dt class="col-sm-3">Occurred At</dt>
                <dd class="col-sm-9">
                    {{ $approvalAuditLog->occurred_at?->format('Y-m-d H:i:s') }}
                </dd>

                <dt class="col-sm-3">User</dt>
                <dd class="col-sm-9">
                    {{ $approvalAuditLog->actorUser?->email ?? 'System' }}
                </dd>

                <dt class="col-sm-3">Employee</dt>
                <dd class="col-sm-9">
                    @if($approvalAuditLog->actorEmployee)
                        {{ $approvalAuditLog->actorEmployee->last_name }},
                        {{ $approvalAuditLog->actorEmployee->first_name }}
                    @else
                        —
                    @endif
                </dd>

                <dt class="col-sm-3">Event</dt>
                <dd class="col-sm-9">
                    {{ $approvalAuditLog->event_type }}
                </dd>

                <dt class="col-sm-3">Record</dt>
                <dd class="col-sm-9">
                    @if($approvalAuditLog->auditable_type)
                        {{ class_basename($approvalAuditLog->auditable_type) }}
                        #{{ $approvalAuditLog->auditable_id }}
                    @else
                        —
                    @endif
                </dd>

                <dt class="col-sm-3">Correlation ID</dt>
                <dd class="col-sm-9">
                    {{ $approvalAuditLog->correlation_id ?? '—' }}
                </dd>

                <dt class="col-sm-3">IP Address</dt>
                <dd class="col-sm-9">
                    {{ $approvalAuditLog->ip_address ?? '—' }}
                </dd>

                <dt class="col-sm-3">User Agent</dt>
                <dd class="col-sm-9">
                    {{ $approvalAuditLog->user_agent ?? '—' }}
                </dd>

                <dt class="col-sm-3">Metadata</dt>
                <dd class="col-sm-9">
                    <pre class="mb-0">{{ json_encode(
                        $approvalAuditLog->metadata,
                        JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
                    ) }}</pre>
                </dd>
            </dl>

            <a
                href="{{ route('approval-audit-logs.index') }}"
                class="btn btn-secondary"
            >
                <i class="fas fa-arrow-left"></i>
                Back
            </a>
        </div>
    </div>
</div>
@endsection