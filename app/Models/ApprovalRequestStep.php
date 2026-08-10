<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApprovalRequestStep extends Model
{
    public const TYPE_SELECTED = 'selected';
    public const TYPE_HR_FINAL = 'hr_final';

    public const STATUS_WAITING = 'waiting';
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'approval_request_id',
        'step_order',
        'canonical_approver_employee_id',
        'step_type',
        'status',
        'activated_at',
        'acted_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'step_order' => 'integer',
            'activated_at' => 'datetime',
            'acted_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(ApprovalRequest::class, 'approval_request_id');
    }

    public function canonicalApprover(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'canonical_approver_employee_id')->withTrashed();
    }

    public function actions(): HasMany
    {
        return $this->hasMany(ApprovalRequestAction::class);
    }
}
