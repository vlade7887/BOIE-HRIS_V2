<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveRequestDay;
use App\Models\LeaveType;
use Illuminate\Support\Facades\DB;

class LeaveRequestService
{
    public function __construct(
        private readonly LeaveFilingValidationService $validation,
        private readonly LeaveOverlapService $overlap,
        private readonly LeaveBalanceService $balances
    ) {
    }

    public function preview(Employee $employee, array $data, ?\Carbon\CarbonImmutable $filingDate = null): array
    {
        $leaveType = LeaveType::query()->whereKey($data['leave_type_id'])->where('is_active', true)->firstOrFail();
        $validated = $this->validation->validate($employee, $leaveType, $data, $filingDate);
        $this->overlap->assertNoBlockingOverlap($employee->id, $validated['start_date']->toDateString(), $validated['end_date']->toDateString());

        $draft = new LeaveRequest([
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'total_units' => $validated['total_units'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
        ]);
        $draft->setRelation('employee', $validated['employee']);
        $draft->setRelation('leaveType', $leaveType);
        $allocation = $this->balances->allocationPreview($draft);

        return [...$validated, 'allocation' => $allocation];
    }

    public function saveDraft(Employee $employee, array $data): LeaveRequest
    {
        return DB::transaction(function () use ($employee, $data) {
            $result = $this->preview($employee, $data);
            $department = $result['employee']->department;
            $draft = LeaveRequest::create([
                'employee_id' => $employee->id,
                'leave_type_id' => $result['leave_type']->id,
                'leave_type_code_snapshot' => $result['leave_type']->code,
                'leave_type_name_snapshot' => $result['leave_type']->name,
                'department_id' => $department?->id,
                'department_code_snapshot' => $department?->department_code,
                'department_name_snapshot' => $department?->department_name,
                'start_date' => $result['start_date']->toDateString(),
                'end_date' => $result['end_date']->toDateString(),
                'total_units' => $result['total_units'],
                'reason' => $data['reason'] ?? null,
                'returned_to_work_date' => $result['returned_to_work_date'],
                'status' => LeaveRequest::STATUS_DRAFT,
            ]);

            foreach ($result['calendar']['days'] as $day) {
                LeaveRequestDay::create([
                    'leave_request_id' => $draft->id,
                    'leave_date' => $day['date'],
                    'is_weekend' => $day['is_weekend'],
                    'is_holiday' => $day['is_holiday'],
                    'holiday_id' => $day['holiday_id'],
                    'holiday_name_snapshot' => $day['holiday_name'],
                    'is_working_day' => $day['is_working_day'],
                    'requested_unit' => $day['requested_unit'],
                    'half_day_period' => $day['half_day_period'],
                    'counted_units' => $day['counted_units'],
                ]);
            }

            return $draft->load(['days', 'leaveType', 'department']);
        });
    }
}
