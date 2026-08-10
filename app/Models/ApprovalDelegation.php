<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApprovalDelegation extends Model
{
    use SoftDeletes;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_REVOKED = 'revoked';
    public const SCOPE_ALL = 'all';
    public const SCOPE_DEPARTMENT = 'department';

    protected $fillable = [
        'acting_for_employee_id',
        'delegate_employee_id',
        'effective_from',
        'effective_until',
        'reason',
        'scope_type',
        'department_id',
        'status',
        'revoked_at',
        'revoked_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'effective_from' => 'date',
            'effective_until' => 'date',
            'revoked_at' => 'datetime',
        ];
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_ACTIVE,
            self::STATUS_EXPIRED,
            self::STATUS_REVOKED,
        ];
    }

    public static function scopeTypes(): array
    {
        return [self::SCOPE_ALL, self::SCOPE_DEPARTMENT];
    }

    public function actingFor(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'acting_for_employee_id');
    }

    public function delegate(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'delegate_employee_id');
    }

    public function revokedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revoked_by_user_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
