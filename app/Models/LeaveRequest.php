<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    use SoftDeletes;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'employee_id', 'leave_type_id', 'leave_type_code_snapshot', 'leave_type_name_snapshot',
        'department_id', 'department_code_snapshot', 'department_name_snapshot',
        'start_date', 'end_date', 'total_units', 'reason', 'returned_to_work_date',
        'status', 'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'returned_to_work_date' => 'date',
            'total_units' => 'decimal:2',
            'submitted_at' => 'datetime',
        ];
    }

    public function employee(): BelongsTo { return $this->belongsTo(Employee::class); }
    public function leaveType(): BelongsTo { return $this->belongsTo(LeaveType::class)->withTrashed(); }
    public function department(): BelongsTo { return $this->belongsTo(Department::class)->withTrashed(); }
    public function days(): HasMany { return $this->hasMany(LeaveRequestDay::class); }
    public function reservations(): HasMany { return $this->hasMany(LeaveBalanceReservation::class); }
    public function ledgerEntries(): HasMany { return $this->hasMany(LeaveBalanceLedger::class); }

    public static function blockingStatuses(): array
    {
        return [self::STATUS_PENDING, self::STATUS_APPROVED];
    }
}
