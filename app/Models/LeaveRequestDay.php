<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequestDay extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'leave_request_id', 'leave_date', 'is_weekend', 'is_holiday', 'holiday_id',
        'holiday_name_snapshot', 'is_working_day', 'requested_unit', 'half_day_period', 'counted_units',
    ];

    protected function casts(): array
    {
        return [
            'leave_date' => 'date',
            'is_weekend' => 'boolean',
            'is_holiday' => 'boolean',
            'is_working_day' => 'boolean',
            'requested_unit' => 'decimal:2',
            'counted_units' => 'decimal:2',
        ];
    }

    public function leaveRequest(): BelongsTo { return $this->belongsTo(LeaveRequest::class); }
    public function holiday(): BelongsTo { return $this->belongsTo(Holiday::class)->withTrashed(); }
}
