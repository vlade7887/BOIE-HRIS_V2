<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalRequestAction extends Model
{
    public const ACTION_SUBMIT = 'submit';
    public const ACTION_APPROVE = 'approve';
    public const ACTION_REJECT = 'reject';
    public const ACTION_CANCEL = 'cancel';

    public const UPDATED_AT = null;

    protected $fillable = [
        'approval_request_id',
        'approval_request_step_id',
        'action',
        'actor_user_id',
        'actor_employee_id',
        'canonical_approver_employee_id',
        'acting_for_employee_id',
        'approval_delegation_id',
        'remarks',
        'acted_at',
        'ip_address',
        'user_agent',
        'idempotency_key',
    ];

    protected function casts(): array
    {
        return ['acted_at' => 'datetime'];
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(ApprovalRequest::class, 'approval_request_id');
    }

    public function step(): BelongsTo
    {
        return $this->belongsTo(ApprovalRequestStep::class, 'approval_request_step_id');
    }

    public function actorUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    public function actorEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'actor_employee_id')->withTrashed();
    }

    public function canonicalApprover(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'canonical_approver_employee_id')->withTrashed();
    }

    public function actingFor(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'acting_for_employee_id')->withTrashed();
    }

    public function delegation(): BelongsTo
    {
        return $this->belongsTo(ApprovalDelegation::class, 'approval_delegation_id')->withTrashed();
    }
}
