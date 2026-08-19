<?php

namespace Database\Seeders;

use App\Models\LeaveType;
use Illuminate\Database\Seeder;

class LeaveTypeSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'VL' => ['name' => 'Vacation Leave', 'annual_entitlement_days' => 15, 'filing_timing' => 'advance', 'minimum_advance_days' => 3, 'carryover_policy' => 'grace_period', 'carryover_grace_days' => 3],
            'SL' => ['name' => 'Sick Leave', 'annual_entitlement_days' => 15, 'filing_timing' => 'after_return', 'minimum_advance_days' => 0, 'carryover_policy' => 'payout', 'carryover_grace_days' => 0],
            'EL' => ['name' => 'Emergency Leave', 'annual_entitlement_days' => 10, 'filing_timing' => 'same_day', 'minimum_advance_days' => 0, 'carryover_policy' => 'expire', 'carryover_grace_days' => 0],
        ] as $code => $data) {
            $leaveType = LeaveType::withTrashed()->firstOrNew(['code' => $code]);
            if ($leaveType->trashed()) {
                $leaveType->restore();
            }
            $leaveType->fill([...$data, 'allows_half_day' => true, 'requires_attachment' => false, 'is_active' => true]);
            $leaveType->save();
        }
    }
}
