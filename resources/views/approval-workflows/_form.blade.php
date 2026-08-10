@include('partials.master-data.validation-summary')

<div class="row g-3">
    <div class="col-md-4">
        <label for="code" class="form-label">Code</label>

        <input
            type="text"
            name="code"
            id="code"
            value="{{ old('code', $approvalWorkflow->code) }}"
            class="form-control @error('code') is-invalid @enderror"
        >

        @error('code')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror
    </div>

    <div class="col-md-2">
        <label for="version" class="form-label">Version</label>

        <input
            type="number"
            name="version"
            id="version"
            min="1"
            value="{{ old('version', $approvalWorkflow->version ?? 1) }}"
            class="form-control @error('version') is-invalid @enderror"
        >

        @error('version')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="name" class="form-label">Name</label>

        <input
            type="text"
            name="name"
            id="name"
            value="{{ old('name', $approvalWorkflow->name) }}"
            class="form-control @error('name') is-invalid @enderror"
        >

        @error('name')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror
    </div>

    <div class="col-12">
        <label for="description" class="form-label">Description</label>

        <textarea
            name="description"
            id="description"
            rows="3"
            class="form-control @error('description') is-invalid @enderror"
        >{{ old('description', $approvalWorkflow->description) }}</textarea>

        @error('description')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="module_key" class="form-label">Module</label>
        <select name="module_key" id="module_key" class="form-select @error('module_key') is-invalid @enderror">
            <option value="">Select module</option>
            @foreach(\App\Models\ApprovalWorkflow::moduleKeys() as $moduleKey)
                <option value="{{ $moduleKey }}" @selected(old('module_key', $approvalWorkflow->module_key) === $moduleKey)>{{ ucfirst(str_replace('_', ' ', $moduleKey)) }}</option>
            @endforeach
        </select>
        @error('module_key')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-2">
        <label for="min_approvers" class="form-label">Min Approvers</label>
        <input type="number" name="min_approvers" id="min_approvers" min="1" max="20" value="{{ old('min_approvers', $approvalWorkflow->min_approvers ?? 1) }}" class="form-control @error('min_approvers') is-invalid @enderror">
        @error('min_approvers')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-2">
        <label for="max_approvers" class="form-label">Max Approvers</label>
        <input type="number" name="max_approvers" id="max_approvers" min="1" max="20" value="{{ old('max_approvers', $approvalWorkflow->max_approvers ?? 5) }}" class="form-control @error('max_approvers') is-invalid @enderror">
        @error('max_approvers')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4">
        <label for="hr_final_approver_employee_id" class="form-label">HR Final Approver</label>
        <select name="hr_final_approver_employee_id" id="hr_final_approver_employee_id" class="form-select @error('hr_final_approver_employee_id') is-invalid @enderror">
            <option value="">Select eligible employee</option>
            @foreach($eligibleApprovers as $employee)
                <option value="{{ $employee->id }}" @selected(old('hr_final_approver_employee_id', $approvalWorkflow->hr_final_approver_employee_id) == $employee->id)>{{ $employee->last_name }}, {{ $employee->first_name }} ({{ $employee->employee_no }})</option>
            @endforeach
        </select>
        @error('hr_final_approver_employee_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4">
        <input type="hidden" name="hr_final_required" value="0">
        <div class="form-check mt-4">
            <input type="checkbox" name="hr_final_required" id="hr_final_required" value="1" class="form-check-input @error('hr_final_required') is-invalid @enderror" @checked(old('hr_final_required', $approvalWorkflow->hr_final_required ?? true))>
            <label for="hr_final_required" class="form-check-label">Require HR final approver</label>
        </div>
        @error('hr_final_required')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>

    <div class="col-md-4">
        <label for="status" class="form-label">Status</label>

        <select
            name="status"
            id="status"
            class="form-select @error('status') is-invalid @enderror"
        >
            <option
                value="draft"
                @selected(old('status', $approvalWorkflow->status ?? 'draft') === 'draft')
            >
                Draft
            </option>

            <option
                value="active"
                @selected(old('status', $approvalWorkflow->status ?? 'draft') === 'active')
            >
                Active
            </option>

            <option
                value="inactive"
                @selected(old('status', $approvalWorkflow->status ?? 'draft') === 'inactive')
            >
                Inactive
            </option>
        </select>

        @error('status')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror
    </div>
</div>
