<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveBalanceReservation extends Model
{
    public const STATUS_RESERVED = 'reserved';
    public const STATUS_RELEASED = 'released';
    public const STATUS_CONSUMED = 'consumed';

    protected $fillable = [
        'leave_request_id', 'leave_entitlement_id', 'reserved_days', 'status',
        'released_at', 'consumed_at',
    ];

    protected function casts(): array
    {
        return [
            'reserved_days' => 'decimal:2',
            'released_at' => 'datetime',
            'consumed_at' => 'datetime',
        ];
    }

    public function leaveRequest(): BelongsTo { return $this->belongsTo(LeaveRequest::class); }
    public function entitlement(): BelongsTo { return $this->belongsTo(LeaveEntitlement::class, 'leave_entitlement_id'); }
}
