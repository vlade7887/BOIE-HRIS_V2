<?php

namespace App\Services;

use App\Models\LeaveEntitlement;
use App\Models\LeaveRequest;
use Carbon\CarbonImmutable;

class LeaveBalanceService
{
    public function __construct(private readonly LeaveEntitlementService $entitlements) {}

    public function available(LeaveEntitlement $entitlement): float
    {
        return round(
            (float) $entitlement->granted_days
            - (float) $entitlement->reserved_days
            - (float) $entitlement->consumed_days
            - (float) $entitlement->expired_days
            - (float) $entitlement->payout_days,
            2
        );
    }

    public function allocationPreview(LeaveRequest $request): array
    {
        $cycle = $this->entitlements->applicableCycle($request->employee, $request->start_date->toDateString());
        $leaveType = $request->leaveType;
        $candidates = $this->candidateEntitlements($cycle, $leaveType, $request->start_date);
        $remaining = (float) $request->total_units;
        $allocations = [];

        foreach ($candidates as $entitlement) {
            $available = $this->available($entitlement);
            $units = min($remaining, max(0, $available));
            if ($units > 0) {
                $allocations[] = ['entitlement' => $entitlement, 'available' => $available, 'units' => round($units, 2)];
                $remaining = round($remaining - $units, 2);
            }
        }

        return [
            'allocations' => $allocations,
            'required_units' => (float) $request->total_units,
            'remaining_units' => $remaining,
            'sufficient' => $remaining <= 0,
        ];
    }

    public function candidateEntitlements($cycle, $leaveType, CarbonImmutable|string $requestDate): array
    {
        if (! $cycle) {
            return [];
        }

        $date = $requestDate instanceof CarbonImmutable
            ? $requestDate
            : CarbonImmutable::parse($requestDate);
        $current = $cycle->entitlements()->where('leave_type_id', $leaveType->id)->first();
        $candidates = [];

        if ($leaveType->carryover_policy === 'grace_period') {
            $graceEnds = CarbonImmutable::parse($cycle->cycle_start_date->toDateString())->addMonthsNoOverflow((int) $leaveType->carryover_grace_days);
            if ($date->lessThan($graceEnds)) {
                $previous = $cycle->previousCycle();
                $old = $previous?->entitlements()->where('leave_type_id', $leaveType->id)->first();
                if ($old) {
                    $candidates[] = $old;
                }
            }
        }

        if ($current) {
            $candidates[] = $current;
        }

        return $candidates;
    }
}
