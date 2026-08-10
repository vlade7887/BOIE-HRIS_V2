<?php

namespace App\Services;

use App\Models\ApprovalAuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ApprovalAuditService
{
    public function record(
        ?User $actor,
        string $eventType,
        ?Model $auditable = null,
        array $metadata = [],
        ?string $correlationId = null
    ): ApprovalAuditLog {
        return ApprovalAuditLog::create([
            'event_type' => $eventType,

            'auditable_type' => $auditable?->getMorphClass(),
            'auditable_id' => $auditable?->getKey(),

            'actor_user_id' => $actor?->id,
            'actor_employee_id' => $actor?->employee?->id,

            'correlation_id' => $correlationId,

            'metadata' => $metadata,

            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),

            'occurred_at' => now(),
        ]);
    }
}