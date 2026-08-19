<?php

namespace App\Services;

use App\Models\LeaveBalanceLedger;
use App\Models\LeaveBalanceReservation;
use App\Models\LeaveEntitlement;
use App\Models\LeaveRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LeaveBalanceReservationService
{
    public function __construct(
        private readonly LeaveBalanceService $balances,
        private readonly LeaveOverlapService $overlap
    ) {
    }

    public function reserve(LeaveRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $request = LeaveRequest::query()->lockForUpdate()->with(['employee', 'leaveType'])->findOrFail($request->id);
            $existing = $request->reservations()->lockForUpdate()->get();
            if ($existing->isNotEmpty()) {
                if ($existing->every(fn ($reservation) => $reservation->status === LeaveBalanceReservation::STATUS_RESERVED)) {
                    return $existing;
                }
                throw ValidationException::withMessages(['reservation' => 'This Leave request already has a finalized reservation history.']);
            }

            if ($request->status !== LeaveRequest::STATUS_PENDING) {
                throw ValidationException::withMessages(['request' => 'Only an internal pending Leave request may reserve balance.']);
            }

            $this->overlap->assertNoBlockingOverlap($request->employee_id, $request->start_date->toDateString(), $request->end_date->toDateString(), $request->id);
            $preview = $this->balances->allocationPreview($request);
            if (! $preview['sufficient']) {
                throw ValidationException::withMessages(['balance' => 'Insufficient Leave balance. No reservation was created.']);
            }

            $locked = collect();
            foreach ($preview['allocations'] as $allocation) {
                $locked->push(LeaveEntitlement::query()->lockForUpdate()->findOrFail($allocation['entitlement']->id));
            }

            $remaining = (float) $request->total_units;
            foreach ($locked as $entitlement) {
                $available = $this->balances->available($entitlement);
                $units = min($remaining, max(0, $available));
                if ($units > 0) {
                    $entitlement->increment('reserved_days', $units);
                    LeaveBalanceReservation::create([
                        'leave_request_id' => $request->id,
                        'leave_entitlement_id' => $entitlement->id,
                        'reserved_days' => $units,
                        'status' => LeaveBalanceReservation::STATUS_RESERVED,
                    ]);
                    LeaveBalanceLedger::create([
                        'employee_id' => $request->employee_id,
                        'leave_type_id' => $request->leave_type_id,
                        'leave_entitlement_id' => $entitlement->id,
                        'leave_request_id' => $request->id,
                        'transaction_type' => 'reserve',
                        'units' => -$units,
                        'reference_key' => "reserve:{$request->id}:{$entitlement->id}",
                        'effective_date' => $request->start_date->toDateString(),
                        'metadata' => ['status' => LeaveBalanceReservation::STATUS_RESERVED],
                    ]);
                    $remaining = round($remaining - $units, 2);
                }
            }

            if ($remaining > 0) {
                throw ValidationException::withMessages(['balance' => 'Insufficient Leave balance. No reservation was created.']);
            }

            return $request->reservations()->get();
        });
    }
}
