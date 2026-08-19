<?php

namespace App\Services;

use App\Models\LeaveRequest;
use Illuminate\Database\Eloquent\Builder;

class LeaveOverlapService
{
    public function blockingQuery(int $employeeId, string $startDate, string $endDate, ?int $ignoreRequestId = null): Builder
    {
        return LeaveRequest::query()
            ->where('employee_id', $employeeId)
            ->whereIn('status', LeaveRequest::blockingStatuses())
            ->when($ignoreRequestId, fn (Builder $query) => $query->whereKeyNot($ignoreRequestId))
            ->whereDate('start_date', '<=', $endDate)
            ->whereDate('end_date', '>=', $startDate);
    }

    public function hasBlockingOverlap(int $employeeId, string $startDate, string $endDate, ?int $ignoreRequestId = null): bool
    {
        return $this->blockingQuery($employeeId, $startDate, $endDate, $ignoreRequestId)->exists();
    }

    public function assertNoBlockingOverlap(int $employeeId, string $startDate, string $endDate, ?int $ignoreRequestId = null): void
    {
        if ($this->hasBlockingOverlap($employeeId, $startDate, $endDate, $ignoreRequestId)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'start_date' => 'The requested Leave dates overlap an existing pending or approved Leave request.',
            ]);
        }
    }
}
