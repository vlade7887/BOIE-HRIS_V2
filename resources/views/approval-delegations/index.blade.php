@extends('layouts.adminlte')

@section('title', 'Approval Delegation')
@section('page_title', 'Approval Delegation')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header text-end">
            <a href="{{ route('approval-delegations.create') }}" class="btn btn-primary btn-sm">
                New Delegation
            </a>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Acting For</th>
                            <th>Delegate</th>
                            <th>Effective Period</th>
                            <th>Scope</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($approvalDelegations as $delegation)
                            <tr>
                                <td>
                                    {{ $delegation->actingFor->last_name }},
                                    {{ $delegation->actingFor->first_name }}
                                </td>

                                <td>
                                    {{ $delegation->delegate->last_name }},
                                    {{ $delegation->delegate->first_name }}
                                </td>

                                <td>
                                    {{ $delegation->effective_from?->format('Y-m-d') }}
                                    to
                                    {{ $delegation->effective_until?->format('Y-m-d') }}
                                </td>

                                <td>
                                    @if($delegation->scope_type === 'department')
                                        Department: {{ $delegation->department?->department_name ?? 'Not configured' }}
                                    @else
                                        All Approvals
                                    @endif
                                </td>

                                <td>
                                    {{ $delegation->reason }}
                                </td>

                                <td>
                                    @switch($delegation->status)
                                        @case('active')
                                            <span class="badge bg-success">
                                                Active
                                            </span>
                                            @break

                                        @case('expired')
                                            <span class="badge bg-secondary">
                                                Expired
                                            </span>
                                            @break

                                        @case('revoked')
                                            <span class="badge bg-danger">
                                                Revoked
                                            </span>
                                            @break

                                        @default
                                            <span class="badge bg-secondary">
                                                {{ ucfirst($delegation->status) }}
                                            </span>
                                    @endswitch
                                </td>

                                <td class="text-end">
                                    @if($delegation->status === 'active')
                                        <a
                                            href="{{ route('approval-delegations.edit', $delegation) }}"
                                            class="btn btn-sm btn-warning"
                                        >
                                            <i class="fas fa-edit"></i>
                                            Edit
                                        </a>

                                        <form
                                            action="{{ route('approval-delegations.revoke', $delegation) }}"
                                            method="POST"
                                            class="d-inline"
                                            onsubmit="return confirm('Revoke this approval delegation?');"
                                        >
                                            @csrf

                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-secondary"
                                            >
                                                <i class="fas fa-ban"></i>
                                                Revoke
                                            </button>
                                        </form>
                                    @endif

                                    <form
                                        action="{{ route('approval-delegations.archive', $delegation) }}"
                                        method="POST"
                                        class="d-inline"
                                        onsubmit="return confirm('Archive this approval delegation?');"
                                    >
                                        @csrf

                                        <button
                                            type="submit"
                                            class="btn btn-sm btn-danger"
                                        >
                                            <i class="fas fa-archive"></i>
                                            Archive
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center p-4">
                                    No delegations found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
