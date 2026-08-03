@extends('layouts.adminlte')

@section('title', 'Edit Employee 201 File')

@section('page_title', 'Edit Employee 201 File')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('employees.index') }}">Employees</a></li>
    <li class="breadcrumb-item active">Edit Employee</li>
@endsection

@section('content')
@php
    $contact = $employee->employeeContact;
    $address = $employee->employeeAddress;
    $governmentId = $employee->employeeGovernmentId;
@endphp
<div class="container-fluid">
    <form action="{{ route('employees.update', $employee) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="card shadow-sm">
            <div class="card-header">
                <h3 class="card-title mb-0">Employee 201 File</h3>
                <p class="text-muted mb-0">Update employee profile details.</p>
            </div>

            <div class="card-body">
                <div class="row g-4 align-items-start">
                    <div class="col-lg-3">
                        <div class="card border-0 bg-light"><div class="card-body text-center">
                            <div class="rounded-circle bg-white border d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 160px; height: 160px;">
                                <img src="https://placehold.co/160x160/edf2f7/64748b?text=Photo" alt="Profile placeholder" class="img-fluid rounded-circle">
                            </div>
                            <h5 class="mb-1">Profile Picture</h5>
                            <p class="text-muted small mb-0">Placeholder image only. Upload will be implemented later.</p>
                        </div></div>
                    </div>

                    <div class="col-lg-9">
                        <ul class="nav nav-tabs" id="employee201Tabs" role="tablist">
                            <li class="nav-item" role="presentation"><button class="nav-link active" id="personal-tab" data-bs-toggle="tab" data-bs-target="#personal" type="button" role="tab">Personal Information</button></li>
                            <li class="nav-item" role="presentation"><button class="nav-link" id="employment-tab" data-bs-toggle="tab" data-bs-target="#employment" type="button" role="tab">Employment</button></li>
                            <li class="nav-item" role="presentation"><button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact" type="button" role="tab">Contact</button></li>
                            <li class="nav-item" role="presentation"><button class="nav-link" id="address-tab" data-bs-toggle="tab" data-bs-target="#address" type="button" role="tab">Address</button></li>
                            <li class="nav-item" role="presentation"><button class="nav-link" id="government-tab" data-bs-toggle="tab" data-bs-target="#government" type="button" role="tab">Government IDs</button></li>
                            <li class="nav-item" role="presentation"><button class="nav-link" id="emergency-tab" data-bs-toggle="tab" data-bs-target="#emergency" type="button" role="tab">Emergency Contacts</button></li>
                            <li class="nav-item" role="presentation"><button class="nav-link" id="documents-tab" data-bs-toggle="tab" data-bs-target="#documents" type="button" role="tab">Documents</button></li>
                        </ul>

                        <div class="tab-content border border-top-0 rounded-bottom p-4 bg-white">
                            <div class="tab-pane fade show active" id="personal" role="tabpanel">
                                <div class="row g-3">
                                    @foreach(['employee_no' => 'Employee Number', 'biometric_id' => 'Biometric ID', 'last_name' => 'Last Name', 'first_name' => 'First Name', 'middle_name' => 'Middle Name', 'suffix' => 'Suffix', 'nickname' => 'Nickname'] as $field => $label)
                                        <div class="col-md-4"><label class="form-label">{{ $label }}@if(in_array($field, ['employee_no', 'last_name', 'first_name'])) <span class="text-danger">*</span>@endif</label><input type="text" name="{{ $field }}" class="form-control" value="{{ old($field, $employee->{$field}) }}"></div>
                                    @endforeach
                                    <div class="col-md-3"><label class="form-label">Gender <span class="text-danger">*</span></label><select name="gender" class="form-select"><option value="" disabled>Select gender</option>@foreach(['Male', 'Female', 'Prefer not to say'] as $value)<option value="{{ $value }}" @selected(old('gender', $employee->gender) === $value)>{{ $value }}</option>@endforeach</select></div>
                                    <div class="col-md-3"><label class="form-label">Civil Status <span class="text-danger">*</span></label><select name="civil_status" class="form-select"><option value="" disabled>Select civil status</option>@foreach(['Single', 'Married', 'Widowed', 'Separated'] as $value)<option value="{{ $value }}" @selected(old('civil_status', $employee->civil_status) === $value)>{{ $value }}</option>@endforeach</select></div>
                                    <div class="col-md-3"><label class="form-label">Birth Date <span class="text-danger">*</span></label><input type="date" name="birth_date" class="form-control" value="{{ old('birth_date', $employee->birth_date?->format('Y-m-d')) }}"></div>
                                    @foreach(['birth_place' => 'Birth Place', 'nationality' => 'Nationality', 'religion' => 'Religion', 'blood_type' => 'Blood Type'] as $field => $label)
                                        <div class="col-md-3"><label class="form-label">{{ $label }}</label><input type="text" name="{{ $field }}" class="form-control" value="{{ old($field, $employee->{$field} ?: 'Filipino') }}"></div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="tab-pane fade" id="employment" role="tabpanel"><div class="row g-3">
                                @foreach(['companies' => ['company_id', 'company_name', 'Company'], 'bases' => ['base_id', 'base_name', 'Base'], 'units' => ['unit_id', 'unit_name', 'Unit'], 'departments' => ['department_id', 'department_name', 'Department'], 'sections' => ['section_id', 'section_name', 'Section'], 'positions' => ['position_id', 'position_name', 'Position'], 'employmentStatuses' => ['employment_status_id', 'status_name', 'Employment Status'], 'employeeClasses' => ['employee_class_id', 'class_name', 'Employee Class']] as $collection => [$field, $name, $label])
                                    <div class="col-md-6"><label class="form-label">{{ $label }} <span class="text-danger">*</span></label><select name="{{ $field }}" class="form-select"><option value="" disabled>Select {{ strtolower($label) }}</option>@foreach(${$collection} as $item)<option value="{{ $item->id }}" @selected((string) old($field, $employee->{$field}) === (string) $item->id)>{{ $item->{$name} }}</option>@endforeach</select></div>
                                @endforeach
                                @foreach(['date_hired' => 'Date Hired', 'date_regularized' => 'Date Regularized', 'employment_end_date' => 'Employment End Date'] as $field => $label)
                                    <div class="col-md-4"><label class="form-label">{{ $label }}@if($field === 'date_hired') <span class="text-danger">*</span>@endif</label><input type="date" name="{{ $field }}" class="form-control" value="{{ old($field, $employee->{$field}?->format('Y-m-d')) }}"></div>
                                @endforeach
                                @foreach(['immediate_supervisor_id' => 'Immediate Supervisor', 'department_head_id' => 'Department Head'] as $field => $label)
                                    <div class="col-md-6"><label class="form-label">{{ $label }}</label><select name="{{ $field }}" class="form-select"><option value="">Select {{ strtolower($label) }}</option>@foreach($supervisors as $supervisor)<option value="{{ $supervisor->id }}" @selected((string) old($field, $employee->{$field}) === (string) $supervisor->id)>{{ trim($supervisor->last_name . ', ' . $supervisor->first_name) }}</option>@endforeach</select></div>
                                @endforeach
                                <div class="col-12"><label class="form-label">Remarks</label><textarea name="remarks" class="form-control" rows="4">{{ old('remarks', $employee->remarks) }}</textarea></div>
                            </div></div>

                            <div class="tab-pane fade" id="contact" role="tabpanel"><div class="row g-3">
                                @foreach(['mobile_number' => 'Mobile Number', 'alternate_mobile_number' => 'Alternate Mobile', 'telephone_number' => 'Telephone', 'company_email' => 'Company Email', 'personal_email' => 'Personal Email'] as $field => $label)
                                    <div class="col-md-6"><label class="form-label">{{ $label }}</label><input type="{{ str_contains($field, 'email') ? 'email' : 'text' }}" name="{{ $field }}" class="form-control" value="{{ old($field, $contact?->{$field}) }}"></div>
                                @endforeach
                            </div></div>

                            <div class="tab-pane fade" id="address" role="tabpanel"><div class="row g-3">
                                @foreach(['present' => 'Present Address', 'permanent' => 'Permanent Address'] as $prefix => $heading)
                                    <div class="col-12"><h6 class="mb-0">{{ $heading }}</h6></div>
                                    @foreach(['house_number' => 'House Number', 'street' => 'Street', 'barangay' => 'Barangay', 'city' => 'City', 'province' => 'Province', 'zip_code' => 'ZIP Code'] as $field => $label)
                                        @php($name = $prefix . '_' . $field)
                                        <div class="col-md-4"><label class="form-label">{{ $label }}</label><input type="text" name="{{ $name }}" class="form-control" value="{{ old($name, $address?->{$name}) }}"></div>
                                    @endforeach
                                @endforeach
                            </div></div>

                            <div class="tab-pane fade" id="government" role="tabpanel"><div class="row g-3">
                                @foreach(['sss_number' => 'SSS', 'philhealth_number' => 'PhilHealth', 'pagibig_number' => 'Pag-IBIG', 'tin_number' => 'TIN', 'passport_number' => 'Passport', 'driver_license_number' => 'Driver License'] as $field => $label)
                                    <div class="col-md-6"><label class="form-label">{{ $label }}</label><input type="text" name="{{ $field }}" class="form-control" value="{{ old($field, $governmentId?->{$field}) }}"></div>
                                @endforeach
                            </div></div>

                            <div class="tab-pane fade" id="emergency" role="tabpanel"><div class="d-flex justify-content-between align-items-center mb-3"><h5 class="mb-0">Emergency Contacts</h5><button type="button" class="btn btn-outline-primary btn-sm">+ Add Contact</button></div><div class="table-responsive"><table class="table table-bordered align-middle"><thead class="table-light"><tr><th>Name</th><th>Relationship</th><th>Mobile</th><th>Telephone</th><th>Address</th><th>Actions</th></tr></thead><tbody><tr><td colspan="6" class="text-center text-muted">No emergency contacts added yet.</td></tr></tbody></table></div></div>
                            <div class="tab-pane fade" id="documents" role="tabpanel"><div class="d-flex justify-content-between align-items-center mb-3"><h5 class="mb-0">Documents</h5><button type="button" class="btn btn-outline-primary btn-sm">+ Upload Document</button></div><div class="table-responsive"><table class="table table-bordered align-middle"><thead class="table-light"><tr><th>Document Type</th><th>Document Name</th><th>Uploaded At</th><th>Actions</th></tr></thead><tbody><tr><td colspan="4" class="text-center text-muted">No documents uploaded yet.</td></tr></tbody></table></div></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-end gap-2"><a href="{{ route('employees.index') }}" class="btn btn-outline-secondary">Cancel</a><button type="submit" class="btn btn-primary">Save Employee</button></div>
        </div>
    </form>
</div>
@endsection
