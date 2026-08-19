<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveEntitlementCycle extends Model
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'employee_id',
        'cycle_start_date',
        'cycle_end_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'cycle_start_date' => 'date',
            'cycle_end_date' => 'date',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function entitlements(): HasMany
    {
        return $this->hasMany(LeaveEntitlement::class);
    }

    public function previousCycle(): ?self
    {
        return self::query()
            ->where('employee_id', $this->employee_id)
            ->whereDate('cycle_start_date', $this->cycle_start_date->subYear()->toDateString())
            ->first();
    }
}
