@php($employeeData = ['id' => $employee->id, 'name' => trim($employee->first_name.' '.$employee->last_name), 'position' => $employee->position?->position_name, 'employee_no' => $employee->employee_no])
<button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" data-employee="{{ e(json_encode($employeeData)) }}" data-reason="{{ $reason }}">
    <span><strong>{{ $employee->first_name }} {{ $employee->last_name }}</strong><br><small>{{ $employee->position?->position_name ?? 'No position' }} · {{ $reason }}</small></span><span class="badge badge-primary">Add</span>
</button>
