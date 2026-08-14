@extends('layouts.adminlte')

@section('title', 'Approval Request Demo')
@section('page_title', 'Approval Request Demo')
@section('breadcrumb')<li class="breadcrumb-item active">Approval Demo</li>@endsection

@section('content')
<div class="container-fluid">
    <div class="alert alert-info">Development harness only. This does not create a Leave request.</div>

    <div class="row">
        <div class="col-lg-7">
            <div class="card card-primary">
                <div class="card-header"><h3 class="card-title">Request and approval route</h3></div>
                <div class="card-body">
                    <dl class="row mb-4">
                        <dt class="col-sm-4">Requester</dt><dd class="col-sm-8">{{ $requester->first_name }} {{ $requester->last_name }} ({{ $requester->employee_no }})</dd>
                        <dt class="col-sm-4">Workflow</dt><dd class="col-sm-8">{{ $workflow->name }} <span class="text-muted">({{ $workflow->code }} v{{ $workflow->version }})</span></dd>
                        <dt class="col-sm-4">Employee approvers</dt><dd class="col-sm-8">{{ $workflow->min_approvers }} to {{ $workflow->max_approvers }}</dd>
                        <dt class="col-sm-4">HR final approval</dt><dd class="col-sm-8">{{ $workflow->hr_final_required ? 'Required' : 'Not required' }}</dd>
                    </dl>

                    <h5>Suggested Approvers</h5>
                    <div id="suggestions" class="mb-4">
                        @forelse($suggestions as $suggestion)
                            @include('approval-demo._employee', ['employee' => $suggestion['employee'], 'reason' => $suggestion['reason']])
                        @empty
                            <p class="text-muted">No eligible supervisor or department head suggestion is available.</p>
                        @endforelse
                    </div>

                    <h5>Eligible Approver Search</h5>
                    <div class="input-group mb-2">
                        <input id="approver-search" class="form-control" placeholder="Employee no, first name, last name, position, department">
                        <button id="search-button" class="btn btn-outline-primary" type="button">Search</button>
                    </div>
                    <div id="search-results" class="list-group mb-4"></div>

                    <form method="POST" action="{{ $preview ? route('approval-demo.store') : route('approval-demo.preview') }}" id="route-form">
                        @csrf
                        <input type="hidden" name="demo_id" value="{{ $demoId }}">
                        <input type="hidden" name="idempotency_key" value="{{ $idempotencyKey }}">
                        <h5>Selected Approval Route</h5>
                        <p class="small text-muted">Choose {{ $workflow->min_approvers }} to {{ $workflow->max_approvers }} employee approver(s). The order below is authoritative.</p>
                        <ol id="selected-route" class="list-group list-group-numbered mb-3">
                            @foreach($selected as $employee)
                                <li class="list-group-item d-flex justify-content-between align-items-center" data-id="{{ $employee->id }}">
                                    <span><strong>{{ $employee->first_name }} {{ $employee->last_name }}</strong><br><small class="text-muted">{{ $employee->position?->position_name ?? 'No position' }}</small></span>
                                    <span><button type="button" class="btn btn-sm btn-outline-secondary move-up">↑</button> <button type="button" class="btn btn-sm btn-outline-secondary move-down">↓</button> <button type="button" class="btn btn-sm btn-outline-danger remove-approver">Remove</button></span>
                                    <input type="hidden" name="approvers[]" value="{{ $employee->id }}">
                                </li>
                            @endforeach
                        </ol>
                        @if($preview)
                            <div class="alert alert-success"><strong>Preview ready.</strong> HR Final Approval is appended by the system after the selected approvers.</div>
                            <h5>Complete Approval Route</h5>
                            <ol class="list-group list-group-numbered mb-3">
                                @foreach($selected as $employee)<li class="list-group-item"><strong>{{ $employee->first_name }} {{ $employee->last_name }}</strong><br><small>{{ $employee->position?->position_name ?? 'No position' }} · Employee-selected approver</small></li>@endforeach
                                @if($workflow->hr_final_required)<li class="list-group-item list-group-item-warning"><strong>{{ $workflow->hrFinalApprover->first_name }} {{ $workflow->hrFinalApprover->last_name }}</strong><br><small>{{ $workflow->hrFinalApprover->position?->position_name ?? 'HR' }} · HR Final Approval</small></li>@endif
                            </ol>
                        @endif
                        <button class="btn btn-primary" type="submit">{{ $preview ? 'Submit Final Request' : 'Preview Route' }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
(() => {
    const selected = document.getElementById('selected-route');
    const add = (data, reason = '') => {
        if ([...selected.querySelectorAll('[data-id]')].some(el => el.dataset.id == data.id)) return;
        const li = document.createElement('li'); li.className = 'list-group-item d-flex justify-content-between align-items-center'; li.dataset.id = data.id;
        li.innerHTML = `<span><strong>${data.name}</strong><br><small class="text-muted">${data.position || 'No position'}${reason ? ' · ' + reason : ''}</small></span><span><button type="button" class="btn btn-sm btn-outline-secondary move-up">↑</button> <button type="button" class="btn btn-sm btn-outline-secondary move-down">↓</button> <button type="button" class="btn btn-sm btn-outline-danger remove-approver">Remove</button></span><input type="hidden" name="approvers[]" value="${data.id}">`;
        selected.appendChild(li);
    };
    document.querySelectorAll('[data-employee]').forEach(el => el.addEventListener('click', () => add(JSON.parse(el.dataset.employee), el.dataset.reason)));
    document.getElementById('search-button').addEventListener('click', async () => {
        const q = encodeURIComponent(document.getElementById('approver-search').value);
        const results = await fetch(`{{ route('approval-demo.approvers.search') }}?q=${q}`, {headers: {'Accept': 'application/json'}}).then(r => r.json());
        const box = document.getElementById('search-results'); box.innerHTML = '';
        results.forEach(data => { const button = document.createElement('button'); button.type = 'button'; button.className = 'list-group-item list-group-item-action'; button.textContent = `${data.name} (${data.employee_no}) · ${data.position || 'No position'} · ${data.department || 'No department'} — Add`; button.onclick = () => add(data); box.appendChild(button); });
        if (!results.length) box.innerHTML = '<div class="text-muted small">No eligible approvers found.</div>';
    });
    selected.addEventListener('click', event => { const li = event.target.closest('li'); if (!li) return; if (event.target.classList.contains('remove-approver')) li.remove(); if (event.target.classList.contains('move-up') && li.previousElementSibling) li.parentNode.insertBefore(li, li.previousElementSibling); if (event.target.classList.contains('move-down') && li.nextElementSibling) li.parentNode.insertBefore(li.nextElementSibling, li); });
})();
</script>
@endsection
