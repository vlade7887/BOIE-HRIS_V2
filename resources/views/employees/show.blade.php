@extends('layouts.adminlte')

@section('title', 'Employee 201 Profile')

@section('page_title', 'Employee 201 Profile')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('employees.index') }}">Employees</a></li>
    <li class="breadcrumb-item active">{{ $employee->employee_no }}</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <p class="text-muted mb-1">Employee Number: {{ $employee->employee_no ?? 'Not provided' }}</p>
                    <h3 class="mb-1">{{ trim(implode(' ', array_filter([$employee->first_name, $employee->middle_name, $employee->last_name, $employee->suffix]))) }}</h3>
                    <p class="mb-0 text-muted">
                        <span class="me-2"><strong>Biometric ID:</strong> {{ $employee->biometric_id ?? 'Not provided' }}</span>
                        <span class="mx-1">&middot;</span>
                        <span class="me-2"><strong>Position:</strong> {{ $employee->position?->position_name ?? 'Not provided' }}</span>
                        <span class="mx-1">&middot;</span>
                        <span class="me-2"><strong>Department:</strong> {{ $employee->department?->department_name ?? 'Not provided' }}</span>
                        <span class="mx-1">&middot;</span>
                        <span class="me-2"><strong>Employment Status:</strong> {{ $employee->employmentStatus?->status_name ?? 'Not provided' }}</span>
                        <span class="mx-1">&middot;</span>
                        <span class="me-2"><strong>Employee Class:</strong> {{ $employee->employeeClass?->class_name ?? 'Not provided' }}</span>
                    </p>
                </div>
                <span class="badge {{ $employee->is_active ? 'bg-success' : 'bg-secondary' }} fs-6">{{ $employee->is_active ? 'Active' : 'Inactive' }}</span>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header"><h3 class="card-title mb-0">Personal Information</h3></div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5">First Name</dt><dd class="col-sm-7">{{ $employee->first_name ?? 'Not provided' }}</dd>
                        <dt class="col-sm-5">Middle Name</dt><dd class="col-sm-7">{{ $employee->middle_name ?? 'Not provided' }}</dd>
                        <dt class="col-sm-5">Last Name</dt><dd class="col-sm-7">{{ $employee->last_name ?? 'Not provided' }}</dd>
                        <dt class="col-sm-5">Suffix</dt><dd class="col-sm-7">{{ $employee->suffix ?? 'Not provided' }}</dd>
                        <dt class="col-sm-5">Nickname</dt><dd class="col-sm-7">{{ $employee->nickname ?? 'Not provided' }}</dd>
                        <dt class="col-sm-5">Gender</dt><dd class="col-sm-7">{{ $employee->gender ?? 'Not provided' }}</dd>
                        <dt class="col-sm-5">Civil Status</dt><dd class="col-sm-7">{{ $employee->civil_status ?? 'Not provided' }}</dd>
                        <dt class="col-sm-5">Birth Date</dt><dd class="col-sm-7">{{ $employee->birth_date?->format('F j, Y') ?? 'Not provided' }}</dd>
                        <dt class="col-sm-5">Birth Place</dt><dd class="col-sm-7">{{ $employee->birth_place ?? 'Not provided' }}</dd>
                        <dt class="col-sm-5">Nationality</dt><dd class="col-sm-7">{{ $employee->nationality ?? 'Not provided' }}</dd>
                        <dt class="col-sm-5">Religion</dt><dd class="col-sm-7">{{ $employee->religion ?? 'Not provided' }}</dd>
                        <dt class="col-sm-5">Blood Type</dt><dd class="col-sm-7">{{ $employee->blood_type ?? 'Not provided' }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header"><h3 class="card-title mb-0">Employment Information</h3></div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5">Company</dt><dd class="col-sm-7">{{ $employee->company?->company_name ?? 'Not provided' }}</dd>
                        <dt class="col-sm-5">Base</dt><dd class="col-sm-7">{{ $employee->base?->base_name ?? 'Not provided' }}</dd>
                        <dt class="col-sm-5">Unit</dt><dd class="col-sm-7">{{ $employee->unit?->unit_name ?? 'Not provided' }}</dd>
                        <dt class="col-sm-5">Department</dt><dd class="col-sm-7">{{ $employee->department?->department_name ?? 'Not provided' }}</dd>
                        <dt class="col-sm-5">Section</dt><dd class="col-sm-7">{{ $employee->section?->section_name ?? 'Not provided' }}</dd>
                        <dt class="col-sm-5">Position</dt><dd class="col-sm-7">{{ $employee->position?->position_name ?? 'Not provided' }}</dd>
                        <dt class="col-sm-5">Employment Status</dt><dd class="col-sm-7">{{ $employee->employmentStatus?->status_name ?? 'Not provided' }}</dd>
                        <dt class="col-sm-5">Employee Class</dt><dd class="col-sm-7">{{ $employee->employeeClass?->class_name ?? 'Not provided' }}</dd>
                        <dt class="col-sm-5">Date Hired</dt><dd class="col-sm-7">{{ $employee->date_hired?->format('F j, Y') ?? 'Not provided' }}</dd>
                        <dt class="col-sm-5">Date Regularized</dt><dd class="col-sm-7">{{ $employee->date_regularized?->format('F j, Y') ?? 'Not provided' }}</dd>
                        <dt class="col-sm-5">Employment End Date</dt><dd class="col-sm-7">{{ $employee->employment_end_date?->format('F j, Y') ?? 'Not provided' }}</dd>
                        <dt class="col-sm-5">Date Resigned</dt><dd class="col-sm-7">{{ $employee->date_resigned?->format('F j, Y') ?? 'Not provided' }}</dd>
                        <dt class="col-sm-5">Immediate Supervisor</dt><dd class="col-sm-7">{{ $employee->immediateSupervisor ? trim(implode(' ', array_filter([$employee->immediateSupervisor->first_name, $employee->immediateSupervisor->middle_name, $employee->immediateSupervisor->last_name, $employee->immediateSupervisor->suffix]))) : 'Not provided' }}</dd>
                        <dt class="col-sm-5">Department Head</dt><dd class="col-sm-7">{{ $employee->departmentHead ? trim(implode(' ', array_filter([$employee->departmentHead->first_name, $employee->departmentHead->middle_name, $employee->departmentHead->last_name, $employee->departmentHead->suffix]))) : 'Not provided' }}</dd>
                        <dt class="col-sm-5">Remarks</dt><dd class="col-sm-7">{{ $employee->remarks ?? 'Not provided' }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header"><h3 class="card-title mb-0">Contact Information</h3></div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5">Mobile Number</dt><dd class="col-sm-7">{{ $employee->employeeContact?->mobile_number ?? 'Not provided' }}</dd>
                        <dt class="col-sm-5">Alternate Mobile</dt><dd class="col-sm-7">{{ $employee->employeeContact?->alternate_mobile_number ?? 'Not provided' }}</dd>
                        <dt class="col-sm-5">Telephone</dt><dd class="col-sm-7">{{ $employee->employeeContact?->telephone_number ?? 'Not provided' }}</dd>
                        <dt class="col-sm-5">Company Email</dt><dd class="col-sm-7">{{ $employee->employeeContact?->company_email ?? 'Not provided' }}</dd>
                        <dt class="col-sm-5">Personal Email</dt><dd class="col-sm-7">{{ $employee->employeeContact?->personal_email ?? 'Not provided' }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header"><h3 class="card-title mb-0">Government IDs</h3></div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5">SSS</dt><dd class="col-sm-7">{{ $employee->employeeGovernmentId?->masked_sss_number ?? 'Not provided' }}</dd>
                        <dt class="col-sm-5">PhilHealth</dt><dd class="col-sm-7">{{ $employee->employeeGovernmentId?->masked_philhealth_number ?? 'Not provided' }}</dd>
                        <dt class="col-sm-5">Pag-IBIG</dt><dd class="col-sm-7">{{ $employee->employeeGovernmentId?->masked_pagibig_number ?? 'Not provided' }}</dd>
                        <dt class="col-sm-5">TIN</dt><dd class="col-sm-7">{{ $employee->employeeGovernmentId?->masked_tin_number ?? 'Not provided' }}</dd>
                        <dt class="col-sm-5">Passport</dt><dd class="col-sm-7">{{ $employee->employeeGovernmentId?->masked_passport_number ?? 'Not provided' }}</dd>
                        <dt class="col-sm-5">Driver's License</dt><dd class="col-sm-7">{{ $employee->employeeGovernmentId?->masked_driver_license_number ?? 'Not provided' }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header"><h3 class="card-title mb-0">Address Information</h3></div>
                <div class="card-body"><div class="row g-4">
                    @foreach(['present' => 'Current Address', 'permanent' => 'Permanent Address'] as $prefix => $title)
                        <div class="col-lg-6"><h6>{{ $title }}</h6><dl class="row mb-0">
                            @foreach(['house_number' => 'House Number', 'street' => 'Street', 'barangay' => 'Barangay', 'city' => 'City', 'province' => 'Province', 'zip_code' => 'ZIP Code'] as $field => $label)
                                @php($attribute = $prefix . '_' . $field)
                                <dt class="col-sm-5">{{ $label }}</dt><dd class="col-sm-7">{{ $employee->employeeAddress?->{$attribute} ?? 'Not provided' }}</dd>
                            @endforeach
                        </dl></div>
                    @endforeach
                </div></div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header"><h3 class="card-title mb-0">Emergency Contacts</h3></div>
                <div class="card-body">
                    @forelse($employee->employeeEmergencyContacts as $contact)
                        @if($loop->first)<div class="table-responsive"><table class="table table-bordered align-middle mb-0"><thead class="table-light"><tr><th>Name</th><th>Relationship</th><th>Contact Number</th><th>Address</th></tr></thead><tbody>@endif
                        <tr><td>{{ $contact->contact_name }}</td><td>{{ $contact->relationship }}</td><td>{{ $contact->mobile_number ?? $contact->telephone_number ?? 'Not provided' }}</td><td>{{ $contact->address ?? 'Not provided' }}</td></tr>
                        @if($loop->last)</tbody></table></div>@endif
                    @empty
                        <p class="text-muted mb-0">No emergency contacts recorded.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header"><h3 class="card-title mb-0">Employee Documents</h3></div>
                <div class="card-body">
                    @forelse($employee->employeeDocuments as $document)
                        @if($loop->first)<div class="table-responsive"><table class="table table-bordered align-middle mb-0"><thead class="table-light"><tr><th>Document Type</th><th>Document Name</th><th>Description</th><th>Date Uploaded</th></tr></thead><tbody>@endif
                        <tr><td>{{ $document->document_type }}</td><td>{{ $document->document_name }}</td><td>{{ $document->remarks ?? 'Not provided' }}</td><td>{{ $document->uploaded_at?->format('F j, Y') ?? $document->created_at?->format('F j, Y') ?? 'Not provided' }}</td></tr>
                        @if($loop->last)</tbody></table></div>@endif
                    @empty
                        <p class="text-muted mb-0">No documents recorded.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-4">
        <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary">Back to Employee List</a>
        <a href="{{ route('employees.edit', $employee) }}" class="btn btn-primary">Edit Employee</a>
    </div>
</div>
@endsection
