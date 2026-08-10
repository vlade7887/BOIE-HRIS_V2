@extends('layouts.adminlte')

@section('title', $approvalWorkflow->name)
@section('page_title', $approvalWorkflow->name)

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <strong>{{ $approvalWorkflow->code }}</strong>
                <span class="text-muted">
                    · Version {{ $approvalWorkflow->version }}
                </span>

                <span class="ms-2">
                    @switch($approvalWorkflow->status)
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
                                {{ ucfirst($approvalWorkflow->status) }}
                            </span>
                    @endswitch
                </span>
            </div>

            <div>
                <a
                    href="{{ route('approval-workflows.index') }}"
                    class="btn btn-sm btn-secondary"
                >
                    <i class="fas fa-arrow-left"></i>
                    Back
                </a>

                @if($approvalWorkflow->status !== 'active')
                    <a
                        href="{{ route('approval-workflows.edit', $approvalWorkflow) }}"
                        class="btn btn-sm btn-warning"
                    >
                        <i class="fas fa-edit"></i>
                        Edit
                    </a>
                @endif
            </div>
        </div>

        <div class="card-body">
            @if($approvalWorkflow->description)
                <p>{{ $approvalWorkflow->description }}</p>
            @else
                <p class="text-muted">No description.</p>
            @endif

            <hr>

            <dl class="row mb-0">
                <dt class="col-sm-4">Module</dt>
                <dd class="col-sm-8">{{ $approvalWorkflow->module_key ? ucfirst(str_replace('_', ' ', $approvalWorkflow->module_key)) : 'Legacy / not configured' }}</dd>
                <dt class="col-sm-4">Approver limits</dt>
                <dd class="col-sm-8">{{ $approvalWorkflow->min_approvers }} to {{ $approvalWorkflow->max_approvers }}</dd>
                <dt class="col-sm-4">HR final approval</dt>
                <dd class="col-sm-8">{{ $approvalWorkflow->hr_final_required ? 'Required' : 'Not required' }}</dd>
                <dt class="col-sm-4">Configured HR approver</dt>
                <dd class="col-sm-8">{{ $approvalWorkflow->hrFinalApprover ? trim($approvalWorkflow->hrFinalApprover->last_name . ', ' . $approvalWorkflow->hrFinalApprover->first_name) : 'Not configured' }}</dd>
            </dl>
        </div>
    </div>
</div>
@endsection
