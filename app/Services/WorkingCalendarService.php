<?php

namespace App\Services;

use App\Models\Holiday;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use InvalidArgumentException;

class WorkingCalendarService
{
    public const BUSINESS_TIMEZONE = 'Asia/Manila';
    public const HALF_DAY_AM = 'AM';
    public const HALF_DAY_PM = 'PM';

    public function normalizeBusinessDate(DateTimeInterface|string $date): CarbonImmutable
    {
        if (is_string($date)) {
            $date = trim($date);
            $normalized = CarbonImmutable::createFromFormat('!Y-m-d', $date, self::BUSINESS_TIMEZONE);

            if (! $normalized || $normalized->format('Y-m-d') !== $date) {
                throw new InvalidArgumentException('Business date must be a valid YYYY-MM-DD date.');
            }

            return $normalized;
        }

        return CarbonImmutable::instance($date)
            ->setTimezone(self::BUSINESS_TIMEZONE)
            ->startOfDay();
    }

    public function holidayFor(DateTimeInterface|string $date): ?Holiday
    {
        $date = $this->normalizeBusinessDate($date);

        return Holiday::query()
            ->where('is_active', true)
            ->whereDate('holiday_date', $date->toDateString())
            ->first();
    }

    public function isWeekend(DateTimeInterface|string $date): bool
    {
        return $this->normalizeBusinessDate($date)->isWeekend();
    }

    public function isWorkingDay(DateTimeInterface|string $date): bool
    {
        $date = $this->normalizeBusinessDate($date);

        return ! $date->isWeekend() && ! $this->holidayFor($date);
    }

    public function calculateWorkingDates(DateTimeInterface|string $start, DateTimeInterface|string $end): array
    {
        [$start, $end] = $this->range($start, $end);
        $dates = [];

        for ($date = $start; $date->lessThanOrEqualTo($end); $date = $date->addDay()) {
            if ($this->isWorkingDay($date)) {
                $dates[] = $date->toDateString();
            }
        }

        return $dates;
    }

    public function calculateLeaveDays(
        DateTimeInterface|string $start,
        DateTimeInterface|string $end,
        float $requestedUnit = 1.0,
        ?string $halfDayPeriod = null
    ): array {
        [$start, $end] = $this->range($start, $end);
        $requestedUnit = $this->validateUnit($requestedUnit, $halfDayPeriod);
        $days = [];
        $totalUnits = 0.0;

        for ($date = $start; $date->lessThanOrEqualTo($end); $date = $date->addDay()) {
            $holiday = $this->holidayFor($date);
            $isWeekend = $date->isWeekend();
            $isWorkingDay = ! $isWeekend && ! $holiday;
            $countedUnits = $isWorkingDay ? $requestedUnit : 0.0;
            $totalUnits += $countedUnits;

            $days[] = [
                'date' => $date->toDateString(),
                'is_weekend' => $isWeekend,
                'is_holiday' => (bool) $holiday,
                'holiday_id' => $holiday?->id,
                'holiday_name' => $holiday?->name,
                'is_working_day' => $isWorkingDay,
                'requested_unit' => $requestedUnit,
                'half_day_period' => $halfDayPeriod,
                'counted_units' => $countedUnits,
            ];
        }

        return [
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'days' => $days,
            'total_units' => $totalUnits,
        ];
    }

    public function calculateLeaveUnits(
        DateTimeInterface|string $start,
        DateTimeInterface|string $end,
        float $requestedUnit = 1.0,
        ?string $halfDayPeriod = null
    ): float {
        return $this->calculateLeaveDays($start, $end, $requestedUnit, $halfDayPeriod)['total_units'];
    }

    private function range(DateTimeInterface|string $start, DateTimeInterface|string $end): array
    {
        $start = $this->normalizeBusinessDate($start);
        $end = $this->normalizeBusinessDate($end);

        if ($end->lessThan($start)) {
            throw new InvalidArgumentException('End date cannot be before start date.');
        }

        return [$start, $end];
    }

    private function validateUnit(float $requestedUnit, ?string $halfDayPeriod): float
    {
        if (! in_array($requestedUnit, [0.5, 1.0], true)) {
            throw new InvalidArgumentException('Requested unit must be 0.5 or 1.0.');
        }

        if ($requestedUnit === 0.5 && ! in_array($halfDayPeriod, [self::HALF_DAY_AM, self::HALF_DAY_PM], true)) {
            throw new InvalidArgumentException('Half-day leave must specify AM or PM.');
        }

        if ($requestedUnit === 1.0 && $halfDayPeriod !== null) {
            throw new InvalidArgumentException('Full-day leave cannot specify a half-day period.');
        }

        return $requestedUnit;
    }
}
