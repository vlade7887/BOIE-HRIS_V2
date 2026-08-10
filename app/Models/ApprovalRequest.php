<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApprovalRequest extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'requester_employee_id',
        'module_key',
        'approvable_type',
        'approvable_id',
        'approval_workflow_id',
        'workflow_code',
        'workflow_version',
        'workflow_name',
        'request_department_id',
        'status',
        'current_step_order',
        'submitted_at',
        'completed_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'approvable_id' => 'integer',
            'workflow_version' => 'integer',
            'current_step_order' => 'integer',
            'submitted_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_DRAFT,
            self::STATUS_PENDING,
            self::STATUS_APPROVED,
            self::STATUS_REJECTED,
            self::STATUS_CANCELLED,
        ];
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'requester_employee_id')->withTrashed();
    }

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(ApprovalWorkflow::class, 'approval_workflow_id')->withTrashed();
    }

    public function requestDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'request_department_id')->withTrashed();
    }

    public function steps(): HasMany
    {
        return $this->hasMany(ApprovalRequestStep::class);
    }

    public function actions(): HasMany
    {
        return $this->hasMany(ApprovalRequestAction::class);
    }
}
