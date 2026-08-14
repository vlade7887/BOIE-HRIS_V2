<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\LeaveEntitlementCycle;
use App\Models\LeaveType;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class LeaveEntitlementService
{
    public function applicableCycle(Employee $employee, CarbonImmutable|string $businessDate): ?LeaveEntitlementCycle
    {
        $date = $businessDate instanceof CarbonImmutable
            ? $businessDate
            : CarbonImmutable::parse($businessDate);

        if ($employee->trashed() || ! $employee->is_active || ! $employee->date_regularized) {
            return null;
        }

        $regularized = CarbonImmutable::parse($employee->date_regularized->toDateString());
        $firstCycleStart = $this->anniversaryForYear($regularized, $regularized->year + 1);

        if ($date->isBefore($firstCycleStart)) {
            return null;
        }

        $cycleStart = $this->anniversaryForYear($regularized, $date->year);

        if ($cycleStart->isAfter($date)) {
            $cycleStart = $this->anniversaryForYear($regularized, $date->year - 1);
        }

        return DB::transaction(function () use ($employee, $cycleStart) {
            $lockedEmployee = Employee::withTrashed()->lockForUpdate()->find($employee->id);

            if (! $lockedEmployee || $lockedEmployee->trashed() || ! $lockedEmployee->is_active || ! $lockedEmployee->date_regularized) {
                return null;
            }

            $cycle = LeaveEntitlementCycle::query()
                ->where('employee_id', $lockedEmployee->id)
                ->whereDate('cycle_start_date', $cycleStart->toDateString())
                ->first();

            if (! $cycle) {
                $cycle = LeaveEntitlementCycle::create([
                    'employee_id' => $lockedEmployee->id,
                    'cycle_start_date' => $cycleStart->toDateString(),
                    'cycle_end_date' => $cycleStart->addYear()->subDay()->toDateString(),
                    'status' => LeaveEntitlementCycle::STATUS_ACTIVE,
                ]);
            }

            $activeLeaveTypes = LeaveType::query()->where('is_active', true)->get();

            foreach ($activeLeaveTypes as $leaveType) {
                $cycle->entitlements()->firstOrCreate(
                    ['leave_type_id' => $leaveType->id],
                    [
                        'granted_days' => $leaveType->annual_entitlement_days,
                        'reserved_days' => 0,
                        'consumed_days' => 0,
                        'expired_days' => 0,
                        'payout_days' => 0,
                    ]
                );
            }

            return $cycle->load('entitlements.leaveType');
        });
    }

    private function anniversaryForYear(CarbonImmutable $regularized, int $year): CarbonImmutable
    {
        return $regularized->setDate($year, $regularized->month, $regularized->day);
    }
}
