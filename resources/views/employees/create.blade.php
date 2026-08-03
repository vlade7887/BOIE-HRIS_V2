@extends('layouts.adminlte')

@section('title', 'Employee 201 File')

@section('page_title', 'Employee 201 File')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('employees.index') }}">Employees</a></li>
    <li class="breadcrumb-item active">Add Employee</li>
@endsection

@section('content')
<div class="container-fluid">
    <form action="{{ route('employees.store') }}" method="POST">
        @csrf
    <div class="card shadow-sm">
        <div class="card-header">
            <h3 class="card-title mb-0">Employee 201 File</h3>
            <p class="text-muted mb-0">Create a new employee profile.</p>
        </div>

        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                    <h5 class="alert-heading">Please correct the highlighted fields.</h5>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div class="row g-4 align-items-start">
                <div class="col-lg-3">
                    <div class="card border-0 bg-light">
                        <div class="card-body text-center">
                            <div class="rounded-circle bg-white border d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 160px; height: 160px;">
                                <img src="https://placehold.co/160x160/edf2f7/64748b?text=Photo" alt="Profile placeholder" class="img-fluid rounded-circle">
                            </div>
                            <h5 class="mb-1">Profile Picture</h5>
                            <p class="text-muted small mb-0">Placeholder image only. Upload will be implemented later.</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-9">
                    <ul class="nav nav-tabs" id="employee201Tabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="personal-tab" data-bs-toggle="tab" data-bs-target="#personal" type="button" role="tab">Personal Information @if($errors->hasAny(['employee_no', 'last_name', 'first_name', 'gender', 'civil_status', 'birth_date']))<span class="badge bg-danger">!</span>@endif</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="employment-tab" data-bs-toggle="tab" data-bs-target="#employment" type="button" role="tab">Employment @if($errors->hasAny(['company_id', 'base_id', 'unit_id', 'department_id', 'section_id', 'position_id', 'employment_status_id', 'employee_class_id', 'date_hired']))<span class="badge bg-danger">!</span>@endif</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact" type="button" role="tab">Contact</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="address-tab" data-bs-toggle="tab" data-bs-target="#address" type="button" role="tab">Address</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="government-tab" data-bs-toggle="tab" data-bs-target="#government" type="button" role="tab">Government IDs</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="emergency-tab" data-bs-toggle="tab" data-bs-target="#emergency" type="button" role="tab">Emergency Contacts</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="documents-tab" data-bs-toggle="tab" data-bs-target="#documents" type="button" role="tab">Documents</button>
                        </li>
                    </ul>

                    <div class="tab-content border border-top-0 rounded-bottom p-4 bg-white">
                        <div class="tab-pane fade show active" id="personal" role="tabpanel" aria-labelledby="personal-tab">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Employee Number <span class="text-danger">*</span></label>
                                    <input type="text" name="employee_no" class="form-control @error('employee_no') is-invalid @enderror" placeholder="Example: 224-1001" value="{{ old('employee_no') }}">
                                    @error('employee_no')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Biometric ID</label>
                                    <input type="text" name="biometric_id" class="form-control" placeholder="Enter biometric ID" value="{{ old('biometric_id') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Last Name <span class="text-danger">*</span></label>
                                    <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror" placeholder="Enter last name" value="{{ old('last_name') }}">
                                    @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">First Name <span class="text-danger">*</span></label>
                                    <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror" placeholder="Enter first name" value="{{ old('first_name') }}">
                                    @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Middle Name</label>
                                    <input type="text" name="middle_name" class="form-control" placeholder="Enter middle name" value="{{ old('middle_name') }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Suffix</label>
                                    <input type="text" name="suffix" class="form-control" placeholder="e.g. Jr., Sr." value="{{ old('suffix') }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Nickname</label>
                                    <input type="text" name="nickname" class="form-control" placeholder="Enter nickname" value="{{ old('nickname') }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Gender <span class="text-danger">*</span></label>
                                    <select name="gender" class="form-select @error('gender') is-invalid @enderror">
                                        <option value="" {{ old('gender') === null ? 'selected' : '' }} disabled>Select gender</option>
                                        <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                                        <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                                        <option value="Prefer not to say" {{ old('gender') == 'Prefer not to say' ? 'selected' : '' }}>Prefer not to say</option>
                                    </select>
                                    @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Civil Status <span class="text-danger">*</span></label>
                                    <select name="civil_status" class="form-select @error('civil_status') is-invalid @enderror">
                                        <option value="" {{ old('civil_status') === null ? 'selected' : '' }} disabled>Select civil status</option>
                                        <option value="Single" {{ old('civil_status') == 'Single' ? 'selected' : '' }}>Single</option>
                                        <option value="Married" {{ old('civil_status') == 'Married' ? 'selected' : '' }}>Married</option>
                                        <option value="Widowed" {{ old('civil_status') == 'Widowed' ? 'selected' : '' }}>Widowed</option>
                                        <option value="Separated" {{ old('civil_status') == 'Separated' ? 'selected' : '' }}>Separated</option>
                                    </select>
                                    @error('civil_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Birth Date <span class="text-danger">*</span></label>
                                    <input type="date" name="birth_date" class="form-control @error('birth_date') is-invalid @enderror" value="{{ old('birth_date') }}">
                                    @error('birth_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Birth Place</label>
                                    <input type="text" name="birth_place" class="form-control" placeholder="Enter birth place" value="{{ old('birth_place') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Nationality</label>
                                    <input type="text" name="nationality" class="form-control" placeholder="Enter nationality" value="{{ old('nationality', 'Filipino') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Religion</label>
                                    <input type="text" name="religion" class="form-control" placeholder="Enter religion" value="{{ old('religion') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Blood Type</label>
                                    <input type="text" name="blood_type" class="form-control" placeholder="e.g. O+, A-" value="{{ old('blood_type') }}">
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="employment" role="tabpanel" aria-labelledby="employment-tab">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Company <span class="text-danger">*</span></label>
                                    <select name="company_id" class="form-select @error('company_id') is-invalid @enderror">
                                        <option value="" {{ old('company_id') === null ? 'selected' : '' }} disabled>Select company</option>
                                        @foreach($companies as $company)
                                            <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>{{ $company->company_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('company_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Base <span class="text-danger">*</span></label>
                                    <select name="base_id" class="form-select @error('base_id') is-invalid @enderror">
                                        <option value="" {{ old('base_id') === null ? 'selected' : '' }} disabled>Select base</option>
                                        @foreach($bases as $base)
                                            <option value="{{ $base->id }}" {{ old('base_id') == $base->id ? 'selected' : '' }}>{{ $base->base_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('base_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Unit <span class="text-danger">*</span></label>
                                    <select name="unit_id" class="form-select @error('unit_id') is-invalid @enderror">
                                        <option value="" {{ old('unit_id') === null ? 'selected' : '' }} disabled>Select unit</option>
                                        @foreach($units as $unit)
                                            <option value="{{ $unit->id }}" {{ old('unit_id') == $unit->id ? 'selected' : '' }}>{{ $unit->unit_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('unit_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Department <span class="text-danger">*</span></label>
                                    <select name="department_id" class="form-select @error('department_id') is-invalid @enderror">
                                        <option value="" {{ old('department_id') === null ? 'selected' : '' }} disabled>Select department</option>
                                        @foreach($departments as $department)
                                            <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>{{ $department->department_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('department_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Section <span class="text-danger">*</span></label>
                                    <select name="section_id" class="form-select @error('section_id') is-invalid @enderror">
                                        <option value="" {{ old('section_id') === null ? 'selected' : '' }} disabled>Select section</option>
                                        @foreach($sections as $section)
                                            <option value="{{ $section->id }}" {{ old('section_id') == $section->id ? 'selected' : '' }}>{{ $section->section_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('section_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Position <span class="text-danger">*</span></label>
                                    <select name="position_id" class="form-select @error('position_id') is-invalid @enderror">
                                        <option value="" {{ old('position_id') === null ? 'selected' : '' }} disabled>Select position</option>
                                        @foreach($positions as $position)
                                            <option value="{{ $position->id }}" {{ old('position_id') == $position->id ? 'selected' : '' }}>{{ $position->position_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('position_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Employment Status <span class="text-danger">*</span></label>
                                    <select name="employment_status_id" class="form-select @error('employment_status_id') is-invalid @enderror">
                                        <option value="" {{ old('employment_status_id') === null ? 'selected' : '' }} disabled>Select status</option>
                                        @foreach($employmentStatuses as $employmentStatus)
                                            <option value="{{ $employmentStatus->id }}" {{ old('employment_status_id') == $employmentStatus->id ? 'selected' : '' }}>{{ $employmentStatus->status_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('employment_status_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Employee Class <span class="text-danger">*</span></label>
                                    <select name="employee_class_id" class="form-select @error('employee_class_id') is-invalid @enderror">
                                        <option value="" {{ old('employee_class_id') === null ? 'selected' : '' }} disabled>Select class</option>
                                        @foreach($employeeClasses as $employeeClass)
                                            <option value="{{ $employeeClass->id }}" {{ old('employee_class_id') == $employeeClass->id ? 'selected' : '' }}>{{ $employeeClass->class_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('employee_class_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Date Hired <span class="text-danger">*</span></label>
                                    <input type="date" name="date_hired" class="form-control @error('date_hired') is-invalid @enderror" value="{{ old('date_hired') }}">
                                    @error('date_hired')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Date Regularized</label>
                                    <input type="date" name="date_regularized" class="form-control" value="{{ old('date_regularized') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Employment End Date</label>
                                    <input type="date" name="employment_end_date" class="form-control" value="{{ old('employment_end_date') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Immediate Supervisor</label>
                                    <select name="immediate_supervisor_id" class="form-select">
                                        <option value="" {{ old('immediate_supervisor_id') === null ? 'selected' : '' }} disabled>Select supervisor</option>
                                        @foreach($supervisors as $supervisor)
                                            <option value="{{ $supervisor->id }}" {{ old('immediate_supervisor_id') == $supervisor->id ? 'selected' : '' }}>{{ trim(($supervisor->last_name ?? '') . ', ' . ($supervisor->first_name ?? '')) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Department Head</label>
                                    <select name="department_head_id" class="form-select">
                                        <option value="" {{ old('department_head_id') === null ? 'selected' : '' }} disabled>Select department head</option>
                                        @foreach($supervisors as $supervisor)
                                            <option value="{{ $supervisor->id }}" {{ old('department_head_id') == $supervisor->id ? 'selected' : '' }}>{{ trim(($supervisor->last_name ?? '') . ', ' . ($supervisor->first_name ?? '')) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Remarks</label>
                                    <textarea name="remarks" class="form-control" rows="4" placeholder="Add remarks here">{{ old('remarks') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="contact" role="tabpanel" aria-labelledby="contact-tab">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Mobile Number</label>
                                    <input type="text" name="mobile_number" class="form-control" placeholder="Enter mobile number" value="{{ old('mobile_number') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Alternate Mobile</label>
                                    <input type="text" name="alternate_mobile_number" class="form-control" placeholder="Enter alternate mobile number" value="{{ old('alternate_mobile_number') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Telephone Number</label>
                                    <input type="text" name="telephone_number" class="form-control" placeholder="Enter telephone number" value="{{ old('telephone_number') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Company Email</label>
                                    <input type="email" name="company_email" class="form-control" placeholder="Enter company email" value="{{ old('company_email') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Personal Email</label>
                                    <input type="email" name="personal_email" class="form-control" placeholder="Enter personal email" value="{{ old('personal_email') }}">
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="address" role="tabpanel" aria-labelledby="address-tab">
                            <div class="row g-3">
                                <div class="col-12"><h6 class="mb-0">Present Address</h6></div>
                                @foreach(['house_number' => 'House Number', 'street' => 'Street', 'barangay' => 'Barangay', 'city' => 'City', 'province' => 'Province', 'zip_code' => 'ZIP Code'] as $field => $label)
                                    <div class="col-md-4">
                                        <label class="form-label">{{ $label }}</label>
                                        <input type="text" name="present_{{ $field }}" class="form-control" value="{{ old('present_'.$field) }}">
                                    </div>
                                @endforeach
                                <div class="col-12"><h6 class="mb-0">Permanent Address</h6></div>
                                @foreach(['house_number' => 'House Number', 'street' => 'Street', 'barangay' => 'Barangay', 'city' => 'City', 'province' => 'Province', 'zip_code' => 'ZIP Code'] as $field => $label)
                                    <div class="col-md-4">
                                        <label class="form-label">{{ $label }}</label>
                                        <input type="text" name="permanent_{{ $field }}" class="form-control" value="{{ old('permanent_'.$field) }}">
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="tab-pane fade" id="government" role="tabpanel" aria-labelledby="government-tab">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">SSS</label>
                                    <input type="text" name="sss_number" class="form-control" placeholder="Enter SSS number" value="{{ old('sss_number') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">PhilHealth</label>
                                    <input type="text" name="philhealth_number" class="form-control" placeholder="Enter PhilHealth number" value="{{ old('philhealth_number') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Pag-IBIG</label>
                                    <input type="text" name="pagibig_number" class="form-control" placeholder="Enter Pag-IBIG number" value="{{ old('pagibig_number') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">TIN</label>
                                    <input type="text" name="tin_number" class="form-control" placeholder="Enter TIN" value="{{ old('tin_number') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Passport</label>
                                    <input type="text" name="passport_number" class="form-control" placeholder="Enter passport number" value="{{ old('passport_number') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Driver License</label>
                                    <input type="text" name="driver_license_number" class="form-control" placeholder="Enter driver license number" value="{{ old('driver_license_number') }}">
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="emergency" role="tabpanel" aria-labelledby="emergency-tab">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0">Emergency Contacts</h5>
                                <button type="button" class="btn btn-outline-primary btn-sm">+ Add Contact</button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Name</th>
                                            <th>Relationship</th>
                                            <th>Mobile</th>
                                            <th>Telephone</th>
                                            <th>Address</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">No emergency contacts added yet.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="documents" role="tabpanel" aria-labelledby="documents-tab">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0">Documents</h5>
                                <button type="button" class="btn btn-outline-primary btn-sm">+ Upload Document</button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Document Type</th>
                                            <th>Document Name</th>
                                            <th>Uploaded At</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">No documents uploaded yet.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer d-flex justify-content-end gap-2">
            <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Employee</button>
        </div>
    </div>
    </form>
</div>
@endsection
