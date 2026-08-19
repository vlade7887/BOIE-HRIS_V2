<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class LeaveFilingValidationService
{
    public function __construct(
        private readonly WorkingCalendarService $calendar,
        private readonly LeaveEntitlementService $entitlements
    ) {
    }

    public function validate(
        Employee $employee,
        LeaveType $leaveType,
        array $data,
        ?CarbonImmutable $filingDate = null
    ): array {
        $employee = Employee::withTrashed()->with('department')->find($employee->id);

        if (! $employee || $employee->trashed() || ! $employee->is_active) {
            throw ValidationException::withMessages(['employee' => 'The employee is not eligible to file Leave.']);
        }

        $filingDate ??= CarbonImmutable::now(WorkingCalendarService::BUSINESS_TIMEZONE)->startOfDay();
        try {
            $start = $this->calendar->normalizeBusinessDate((string) ($data['start_date'] ?? ''));
            $end = $this->calendar->normalizeBusinessDate((string) ($data['end_date'] ?? ''));
        } catch (InvalidArgumentException $exception) {
            throw ValidationException::withMessages(['start_date' => $exception->getMessage()]);
        }

        if ($end->lessThan($start)) {
            throw ValidationException::withMessages(['end_date' => 'End date cannot be before start date.']);
        }

        $unit = (float) ($data['requested_unit'] ?? 1.0);
        $halfDayPeriod = $data['half_day_period'] ?? null;

        if ($unit === 0.5 && $start->toDateString() !== $end->toDateString()) {
            throw ValidationException::withMessages(['end_date' => 'Half-day Leave must cover one calendar date only.']);
        }

        if ($unit === 0.5 && ! $leaveType->allows_half_day) {
            throw ValidationException::withMessages(['requested_unit' => 'This Leave type does not allow half-day filing.']);
        }

        try {
            $calendarResult = $this->calendar->calculateLeaveDays($start, $end, $unit, $halfDayPeriod);
        } catch (InvalidArgumentException $exception) {
            throw ValidationException::withMessages(['requested_unit' => $exception->getMessage()]);
        }

        if ((float) $calendarResult['total_units'] <= 0) {
            throw ValidationException::withMessages(['start_date' => 'The requested range contains no working Leave units.']);
        }

        $cycle = $this->entitlements->applicableCycle($employee, $start);

        if (! $cycle) {
            throw ValidationException::withMessages(['employee' => 'The employee has no applicable Leave entitlement cycle.']);
        }

        $this->validateTiming($leaveType, $start, $end, $filingDate, $data['returned_to_work_date'] ?? null);

        return [
            'employee' => $employee,
            'leave_type' => $leaveType,
            'cycle' => $cycle,
            'start_date' => $start,
            'end_date' => $end,
            'requested_unit' => $unit,
            'half_day_period' => $halfDayPeriod,
            'calendar' => $calendarResult,
            'total_units' => (float) $calendarResult['total_units'],
            'returned_to_work_date' => $data['returned_to_work_date'] ?? null,
        ];
    }

    private function validateTiming(
        LeaveType $leaveType,
        CarbonImmutable $start,
        CarbonImmutable $end,
        CarbonImmutable $filingDate,
        mixed $returnedToWorkDate
    ): void {
        if ($leaveType->filing_timing === 'advance') {
            $latest = $start->subDays((int) $leaveType->minimum_advance_days);
            if ($filingDate->greaterThan($latest)) {
                throw ValidationException::withMessages([
                    'start_date' => "This Leave must be filed at least {$leaveType->minimum_advance_days} calendar days before the start date.",
                ]);
            }
        }

        if ($leaveType->filing_timing === 'after_return') {
            if (! $returnedToWorkDate) {
                throw ValidationException::withMessages(['returned_to_work_date' => 'Return-to-work date is required for this Leave type.']);
            }
            $returned = $this->calendar->normalizeBusinessDate((string) $returnedToWorkDate);
            if ($returned->lessThanOrEqualTo($end) || $filingDate->lessThanOrEqualTo($end)) {
                throw ValidationException::withMessages(['returned_to_work_date' => 'Sick Leave must be filed after the employee returns to work.']);
            }
        }

        if ($leaveType->filing_timing === 'same_day' && $filingDate->greaterThan($start)) {
            throw ValidationException::withMessages(['start_date' => 'Emergency Leave supports same-day and future filing; retroactive filing is not configured.']);
        }
    }
}
