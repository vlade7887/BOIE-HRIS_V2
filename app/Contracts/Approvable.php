<?php

namespace App\Contracts;

use App\Models\Employee;

interface Approvable
{
    public function approvalModuleKey(): string;

    public function approvalType(): string;

    public function approvalId(): int;

    public function approvalRequester(): Employee;

    public function approvalDepartmentId(): ?int;
}
