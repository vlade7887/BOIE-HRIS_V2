<?php

namespace App\Support;

use App\Contracts\Approvable;
use App\Models\Employee;

final class ApprovalDemoApprovable implements Approvable
{
    public function __construct(
        private readonly int $id,
        private readonly Employee $requester
    ) {
    }

    public function approvalModuleKey(): string { return 'approval_demo'; }
    public function approvalType(): string { return self::class; }
    public function approvalId(): int { return $this->id; }
    public function approvalRequester(): Employee { return $this->requester; }
    public function approvalDepartmentId(): ?int { return $this->requester->department_id; }
}
