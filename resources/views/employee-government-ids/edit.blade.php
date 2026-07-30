@extends('layouts.adminlte')

@section('title', 'Edit Employee Government ID')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Employee Government ID</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('employee-government-ids.update', $employeeGovernmentId) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label for="employee_id">Employee</label>
                            <select name="employee_id" id="employee_id" class="form-control" required>
                                <option value="">Select employee</option>
                                @foreach(App\Models\Employee::orderBy('last_name')->get() as $employee)
                                    <option value="{{ $employee->id }}" {{ $employeeGovernmentId->employee_id == $employee->id ? 'selected' : '' }}>{{ $employee->first_name }} {{ $employee->last_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="sss_number">SSS Number</label>
                            <input type="text" name="sss_number" id="sss_number" class="form-control" value="{{ old('sss_number', $employeeGovernmentId->sss_number) }}">
                        </div>

                        <div class="form-group">
                            <label for="philhealth_number">PhilHealth Number</label>
                            <input type="text" name="philhealth_number" id="philhealth_number" class="form-control" value="{{ old('philhealth_number', $employeeGovernmentId->philhealth_number) }}">
                        </div>

                        <div class="form-group">
                            <label for="pagibig_number">PAG-IBIG Number</label>
                            <input type="text" name="pagibig_number" id="pagibig_number" class="form-control" value="{{ old('pagibig_number', $employeeGovernmentId->pagibig_number) }}">
                        </div>

                        <div class="form-group">
                            <label for="tin_number">TIN</label>
                            <input type="text" name="tin_number" id="tin_number" class="form-control" value="{{ old('tin_number', $employeeGovernmentId->tin_number) }}">
                        </div>

                        <button type="submit" class="btn btn-primary">Update</button>
                        <a href="{{ route('employee-government-ids.index') }}" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
