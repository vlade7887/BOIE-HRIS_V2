@include('partials.master-data.validation-summary')

<div class="row g-3">
    <div class="col-md-6">
        <label for="acting_for_employee_id" class="form-label">Acting For</label>

        <select
            name="acting_for_employee_id"
            id="acting_for_employee_id"
            class="form-select @error('acting_for_employee_id') is-invalid @enderror"
        >
            <option value="">Select employee</option>

            @foreach($employees as $employee)
                <option
                    value="{{ $employee->id }}"
                    @selected(old('acting_for_employee_id', $approvalDelegation->acting_for_employee_id) == $employee->id)
                >
                    {{ $employee->last_name }},
                    {{ $employee->first_name }}
                    ({{ $employee->employee_no }})
                </option>
            @endforeach
        </select>

        @error('acting_for_employee_id')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="delegate_employee_id" class="form-label">Delegate</label>

        <select
            name="delegate_employee_id"
            id="delegate_employee_id"
            class="form-select @error('delegate_employee_id') is-invalid @enderror"
        >
            <option value="">Select employee</option>

            @foreach($employees as $employee)
                <option
                    value="{{ $employee->id }}"
                    @selected(old('delegate_employee_id', $approvalDelegation->delegate_employee_id) == $employee->id)
                >
                    {{ $employee->last_name }},
                    {{ $employee->first_name }}
                    ({{ $employee->employee_no }})
                </option>
            @endforeach
        </select>

        @error('delegate_employee_id')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="effective_from" class="form-label">Effective From</label>

        <input
            type="date"
            name="effective_from"
            id="effective_from"
            value="{{ old('effective_from', $approvalDelegation->effective_from?->format('Y-m-d')) }}"
            class="form-control @error('effective_from') is-invalid @enderror"
        >

        @error('effective_from')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="effective_until" class="form-label">Effective Until</label>

        <input
            type="date"
            name="effective_until"
            id="effective_until"
            value="{{ old('effective_until', $approvalDelegation->effective_until?->format('Y-m-d')) }}"
            class="form-control @error('effective_until') is-invalid @enderror"
        >

        @error('effective_until')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror
    </div>

    <div class="col-12">
        <label for="scope_type" class="form-label">Approval Scope</label>
        <select name="scope_type" id="scope_type" class="form-select @error('scope_type') is-invalid @enderror">
            <option value="all" @selected(old('scope_type', $approvalDelegation->scope_type ?? 'all') === 'all')>All Approvals</option>
            <option value="department" @selected(old('scope_type', $approvalDelegation->scope_type) === 'department')>Specific Department</option>
        </select>
        @error('scope_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-6" id="department-field-wrapper">
        <label for="department_id" class="form-label">Department</label>
        <select name="department_id" id="department_id" class="form-select @error('department_id') is-invalid @enderror" aria-describedby="department-help">
            <option value="">Select department for department scope</option>
            @foreach($departments as $department)
                <option value="{{ $department->id }}" @selected(old('department_id', $approvalDelegation->department_id) == $department->id)>{{ $department->department_name }}</option>
            @endforeach
        </select>
        <div id="department-help" class="form-text">Required for Specific Department scope.</div>
        @error('department_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12">
        <label for="reason" class="form-label">Reason</label>

        <textarea
            name="reason"
            id="reason"
            rows="3"
            class="form-control @error('reason') is-invalid @enderror"
        >{{ old('reason', $approvalDelegation->reason) }}</textarea>

        @error('reason')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror
    </div>

    <input type="hidden" name="status" value="active">
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const scopeField = document.getElementById('scope_type');
        const departmentFieldWrapper = document.getElementById('department-field-wrapper');
        const departmentField = document.getElementById('department_id');

        if (!scopeField || !departmentFieldWrapper || !departmentField) {
            return;
        }

        const toggleDepartmentField = function () {
            const isAllApprovals = scopeField.value === 'all';

            departmentFieldWrapper.hidden = isAllApprovals;
            departmentField.disabled = isAllApprovals;

            if (isAllApprovals) {
                departmentField.value = '';
            }
        };

        scopeField.addEventListener('change', toggleDepartmentField);
        toggleDepartmentField();
    });
</script>
