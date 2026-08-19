<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveType extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'description',
        'annual_entitlement_days',
        'allows_half_day',
        'requires_attachment',
        'filing_timing',
        'minimum_advance_days',
        'carryover_policy',
        'carryover_grace_days',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'annual_entitlement_days' => 'decimal:2',
            'allows_half_day' => 'boolean',
            'requires_attachment' => 'boolean',
            'minimum_advance_days' => 'integer',
            'carryover_grace_days' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function entitlements(): HasMany
    {
        return $this->hasMany(LeaveEntitlement::class);
    }
}
