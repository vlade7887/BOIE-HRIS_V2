@extends('layouts.adminlte')

@section('title', 'Employee Government ID Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Employee Government ID Details</h3>
                    <a href="{{ route('employee-government-ids.index') }}" class="btn btn-secondary btn-sm">Back</a>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Employee</dt>
                        <dd class="col-sm-8">{{ $employeeGovernmentId->employee?->first_name }} {{ $employeeGovernmentId->employee?->last_name }}</dd>

                        <dt class="col-sm-4">SSS Number</dt>
                        <dd class="col-sm-8">{{ $employeeGovernmentId->sss_number ?? '—' }}</dd>

                        <dt class="col-sm-4">PhilHealth Number</dt>
                        <dd class="col-sm-8">{{ $employeeGovernmentId->philhealth_number ?? '—' }}</dd>

                        <dt class="col-sm-4">PAG-IBIG Number</dt>
                        <dd class="col-sm-8">{{ $employeeGovernmentId->pagibig_number ?? '—' }}</dd>

                        <dt class="col-sm-4">TIN</dt>
                        <dd class="col-sm-8">{{ $employeeGovernmentId->tin_number ?? '—' }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
