@extends('layouts.adminlte')

@section('title', 'Approval Workflows')
@section('page_title', 'Approval Workflows')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header text-end">
            <a href="{{ route('approval-workflows.create') }}" class="btn btn-primary btn-sm">
                New Workflow
            </a>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Version</th>
                            <th>Name</th>
                            <th>Module</th>
                            <th>Approver Limits</th>
                            <th>HR Final</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($approvalWorkflows as $workflow)
                            <tr>
                                <td>{{ $workflow->code }}</td>

                                <td>
                                    v{{ $workflow->version }}
                                </td>

                                <td>{{ $workflow->name }}</td>

                                <td>{{ $workflow->module_key ? ucfirst(str_replace('_', ' ', $workflow->module_key)) : 'Legacy / not configured' }}</td>

                                <td>{{ $workflow->min_approvers }}–{{ $workflow->max_approvers }}</td>

                                <td>{{ $workflow->hr_final_required ? ($workflow->hrFinalApprover?->employee_no ?? 'Required') : 'Not required' }}</td>

                                <td>
                                    @switch($workflow->status)
                                        @case('active')
                                            <span class="badge bg-success">Active</span>
                                            @break

                                        @case('draft')
                                            <span class="badge bg-warning text-dark">Draft</span>
                                            @break

                                        @case('inactive')
                                            <span class="badge bg-secondary">Inactive</span>
                                            @break

                                        @case('archived')
                                            <span class="badge bg-danger">Archived</span>
                                            @break

                                        @default
                                            <span class="badge bg-secondary">
                                                {{ ucfirst($workflow->status) }}
                                            </span>
                                    @endswitch
                                </td>

                                <td class="text-end">
                                    <a
                                        href="{{ route('approval-workflows.show', $workflow) }}"
                                        class="btn btn-sm btn-primary"
                                    >
                                        <i class="fas fa-eye"></i>
                                        View
                                    </a>

                                    @if($workflow->status !== 'active')
                                        <a
                                            href="{{ route('approval-workflows.edit', $workflow) }}"
                                            class="btn btn-sm btn-warning"
                                        >
                                            <i class="fas fa-edit"></i>
                                            Edit
                                        </a>
                                    @endif

                                    <form
                                        action="{{ route('approval-workflows.archive', $workflow) }}"
                                        method="POST"
                                        class="d-inline"
                                        onsubmit="return confirm('Archive this approval workflow?');"
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
                                <td colspan="8" class="text-center p-4">
                                    No approval workflows found.
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
