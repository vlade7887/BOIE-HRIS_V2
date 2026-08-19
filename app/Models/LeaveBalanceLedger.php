<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveBalanceLedger extends Model
{
    public $timestamps = false;

    protected $table = 'leave_balance_ledger';

    protected $fillable = [
        'employee_id', 'leave_type_id', 'leave_entitlement_id', 'leave_request_id',
        'transaction_type', 'units', 'reference_key', 'effective_date', 'metadata', 'remarks',
    ];

    protected function casts(): array
    {
        return [
            'units' => 'decimal:2',
            'effective_date' => 'date',
            'metadata' => 'array',
        ];
    }

    public function employee(): BelongsTo { return $this->belongsTo(Employee::class); }
    public function leaveType(): BelongsTo { return $this->belongsTo(LeaveType::class)->withTrashed(); }
    public function entitlement(): BelongsTo { return $this->belongsTo(LeaveEntitlement::class, 'leave_entitlement_id'); }
    public function leaveRequest(): BelongsTo { return $this->belongsTo(LeaveRequest::class); }
}
