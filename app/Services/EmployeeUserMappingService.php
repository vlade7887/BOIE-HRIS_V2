<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class EmployeeUserMappingService
{
    public function __construct(
        private readonly ApprovalAuditService $audit
    ) {
    }

    public function map(
        Employee $employee,
        ?User $user,
        ?User $actor
    ): Employee {
        return DB::transaction(function () use ($employee, $user, $actor) {
            $oldUserId = $employee->user_id;

            $employee->user_id = $user?->id;
            $employee->save();

            $this->audit->record(
                $actor,
                'employee_user_mapping.updated',
                $employee,
                [
                    'old_user_id' => $oldUserId,
                    'new_user_id' => $user?->id,
                ]
            );

            return $employee->refresh();
        });
    }
}