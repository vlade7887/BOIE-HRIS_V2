@extends('layouts.adminlte')

@section('title', 'Approval Audit Log')
@section('page_title', 'Approval Audit Log')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>User</th>
                            <th>Employee</th>
                            <th>Event</th>
                            <th>Record</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($approvalAuditLogs as $log)
                            <tr>
                                <td>
                                    {{ $log->occurred_at?->format('Y-m-d H:i:s') }}
                                </td>

                                <td>
                                    {{ $log->actorUser?->email ?? 'System' }}
                                </td>

                                <td>
                                    @if($log->actorEmployee)
                                        {{ $log->actorEmployee->last_name }},
                                        {{ $log->actorEmployee->first_name }}
                                    @else
                                        —
                                    @endif
                                </td>

                                <td>
                                    {{ $log->event_type }}
                                </td>

                                <td>
                                    @if($log->auditable_type)
                                        {{ class_basename($log->auditable_type) }}
                                        #{{ $log->auditable_id }}
                                    @else
                                        —
                                    @endif
                                </td>

                                <td class="text-end">
                                    <a
                                        href="{{ route('approval-audit-logs.show', $log) }}"
                                        class="btn btn-sm btn-primary"
                                    >
                                        <i class="fas fa-eye"></i>
                                        View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center p-4">
                                    No audit records found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer">
            {{ $approvalAuditLogs->links() }}
        </div>
    </div>
</div>
@endsection