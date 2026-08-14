<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveEntitlement extends Model
{
    protected $fillable = [
        'leave_entitlement_cycle_id',
        'leave_type_id',
        'granted_days',
        'reserved_days',
        'consumed_days',
        'expired_days',
        'payout_days',
    ];

    protected function casts(): array
    {
        return [
            'granted_days' => 'decimal:2',
            'reserved_days' => 'decimal:2',
            'consumed_days' => 'decimal:2',
            'expired_days' => 'decimal:2',
            'payout_days' => 'decimal:2',
        ];
    }

    public function cycle(): BelongsTo
    {
        return $this->belongsTo(LeaveEntitlementCycle::class, 'leave_entitlement_cycle_id');
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class)->withTrashed();
    }

    public function getAvailableDaysAttribute(): string
    {
        return number_format(
            (float) $this->granted_days
            - (float) $this->reserved_days
            - (float) $this->consumed_days
            - (float) $this->expired_days
            - (float) $this->payout_days,
            2,
            '.',
            ''
        );
    }
}
