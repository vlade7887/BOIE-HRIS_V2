<?php

namespace Database\Seeders;

use App\Models\LeaveType;
use Illuminate\Database\Seeder;

class LeaveTypeSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'VL' => ['name' => 'Vacation Leave', 'annual_entitlement_days' => 15],
            'SL' => ['name' => 'Sick Leave', 'annual_entitlement_days' => 15],
            'EL' => ['name' => 'Emergency Leave', 'annual_entitlement_days' => 10],
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
