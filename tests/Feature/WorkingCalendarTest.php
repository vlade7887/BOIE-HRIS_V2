<?php

namespace Tests\Feature;

use App\Models\Holiday;
use App\Models\User;
use App\Services\WorkingCalendarService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class WorkingCalendarTest extends TestCase
{
    use RefreshDatabase;

    public function test_holiday_can_be_created_and_updated_while_retaining_its_date(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('holidays.store'), [
            'holiday_date' => '2026-08-21', 'name' => 'QA Holiday', 'holiday_type' => 'regular', 'is_active' => true,
        ])->assertRedirect(route('holidays.index'));

        $holiday = Holiday::firstOrFail();
        $this->actingAs($user)->put(route('holidays.update', $holiday), [
            'holiday_date' => '2026-08-21', 'name' => 'Updated QA Holiday', 'holiday_type' => 'regular', 'is_active' => true,
        ])->assertRedirect(route('holidays.index'));

        $this->assertSame('2026-08-21', $holiday->fresh()->holiday_date->toDateString());
        $this->assertDatabaseHas('holidays', ['id' => $holiday->id, 'name' => 'Updated QA Holiday']);
    }

    public function test_duplicate_holiday_date_is_rejected(): void
    {
        $user = User::factory()->create();
        Holiday::create(['holiday_date' => '2026-08-21', 'name' => 'Existing Holiday']);

        $response = $this->actingAs($user)->post(route('holidays.store'), [
            'holiday_date' => '2026-08-21', 'name' => 'Duplicate Holiday',
        ]);

        $response->assertSessionHasErrors(['holiday_date']);
        $this->assertDatabaseCount('holidays', 1);
    }

    public function test_holiday_can_be_archived_and_restored_and_has_no_hard_delete_route(): void
    {
        $user = User::factory()->create();
        $holiday = Holiday::create(['holiday_date' => '2026-08-21', 'name' => 'Restorable Holiday']);

        $this->actingAs($user)->post(route('holidays.archive', $holiday))->assertRedirect();
        $this->assertSoftDeleted('holidays', ['id' => $holiday->id]);
        $this->actingAs($user)->post(route('holidays.restore', $holiday->id))->assertRedirect();
        $this->assertDatabaseHas('holidays', ['id' => $holiday->id, 'deleted_at' => null]);
        $this->assertFalse(collect(app('router')->getRoutes())->contains(fn ($route) => $route->getName() === 'holidays.destroy'));
    }

    public function test_archived_holiday_date_remains_unique(): void
    {
        $user = User::factory()->create();
        $holiday = Holiday::create(['holiday_date' => '2026-08-21', 'name' => 'Archived Holiday']);
        $this->actingAs($user)->post(route('holidays.archive', $holiday));

        $response = $this->actingAs($user)->post(route('holidays.store'), [
            'holiday_date' => '2026-08-21', 'name' => 'Replacement Holiday',
        ]);

        $response->assertSessionHasErrors(['holiday_date']);
    }

    public function test_normal_weekdays_weekends_and_active_holidays_are_classified(): void
    {
        $calendar = app(WorkingCalendarService::class);
        Holiday::create(['holiday_date' => '2026-08-21', 'name' => 'Friday Holiday', 'is_active' => true]);

        $this->assertTrue($calendar->isWorkingDay('2026-08-20'));
        $this->assertFalse($calendar->isWorkingDay('2026-08-22'));
        $this->assertFalse($calendar->isWorkingDay('2026-08-23'));
        $this->assertFalse($calendar->isWorkingDay('2026-08-21'));
        $this->assertTrue($calendar->isWeekend('2026-08-22'));
        $this->assertNotNull($calendar->holidayFor('2026-08-21'));
    }

    public function test_inactive_or_archived_holidays_do_not_affect_working_day_calculation(): void
    {
        $calendar = app(WorkingCalendarService::class);
        $holiday = Holiday::create(['holiday_date' => '2026-08-21', 'name' => 'Toggle Holiday', 'is_active' => false]);

        $this->assertTrue($calendar->isWorkingDay('2026-08-21'));
        $holiday->update(['is_active' => true]);
        $this->assertFalse($calendar->isWorkingDay('2026-08-21'));
        $holiday->delete();
        $this->assertTrue($calendar->isWorkingDay('2026-08-21'));
        $holiday->restore();
        $this->assertFalse($calendar->isWorkingDay('2026-08-21'));
    }

    public function test_working_date_range_is_inclusive_and_handles_same_day_month_year_and_leap_year(): void
    {
        $calendar = app(WorkingCalendarService::class);

        $this->assertSame(['2026-08-21'], $calendar->calculateWorkingDates('2026-08-21', '2026-08-21'));
        $this->assertSame([], $calendar->calculateWorkingDates('2026-08-22', '2026-08-22'));
        $this->assertSame(['2026-08-31', '2026-09-01'], $calendar->calculateWorkingDates('2026-08-31', '2026-09-01'));
        $this->assertSame(['2026-12-31', '2027-01-01'], $calendar->calculateWorkingDates('2026-12-31', '2027-01-01'));
        $this->assertSame(['2028-02-28', '2028-02-29', '2028-03-01'], $calendar->calculateWorkingDates('2028-02-28', '2028-03-01'));
    }

    public function test_leave_day_result_supports_full_and_half_day_units_and_snapshot_fields(): void
    {
        $calendar = app(WorkingCalendarService::class);
        Holiday::create(['holiday_date' => '2026-08-21', 'name' => 'Snapshot Holiday']);

        $full = $calendar->calculateLeaveDays('2026-08-20', '2026-08-22');
        $half = $calendar->calculateLeaveDays('2026-08-20', '2026-08-20', 0.5, WorkingCalendarService::HALF_DAY_AM);
        $halfPm = $calendar->calculateLeaveDays('2026-08-20', '2026-08-20', 0.5, WorkingCalendarService::HALF_DAY_PM);
        $holidayHalf = $calendar->calculateLeaveDays('2026-08-21', '2026-08-21', 0.5, WorkingCalendarService::HALF_DAY_PM);
        $weekendHalf = $calendar->calculateLeaveDays('2026-08-22', '2026-08-22', 0.5, WorkingCalendarService::HALF_DAY_PM);

        $this->assertSame(1.0, $full['total_units']);
        $this->assertSame(0.5, $half['total_units']);
        $this->assertSame(0.5, $halfPm['total_units']);
        $this->assertSame(0.0, $holidayHalf['total_units']);
        $this->assertSame(0.0, $weekendHalf['total_units']);
        $this->assertSame(['date', 'is_weekend', 'is_holiday', 'holiday_id', 'holiday_name', 'is_working_day', 'requested_unit', 'half_day_period', 'counted_units'], array_keys($half['days'][0]));
        $this->assertSame('AM', $half['days'][0]['half_day_period']);
        $this->assertSame('Snapshot Holiday', $holidayHalf['days'][0]['holiday_name']);
    }

    public function test_calendar_rejects_invalid_units_periods_and_ranges(): void
    {
        $calendar = app(WorkingCalendarService::class);

        $this->expectException(InvalidArgumentException::class);
        $calendar->calculateLeaveDays('2026-08-21', '2026-08-20');
    }

    public function test_calendar_rejects_invalid_half_day_and_fractional_units(): void
    {
        $calendar = app(WorkingCalendarService::class);

        try {
            $calendar->calculateLeaveDays('2026-08-20', '2026-08-20', 0.5);
            $this->fail('Missing half-day period was accepted.');
        } catch (InvalidArgumentException) {
        }

        try {
            $calendar->calculateLeaveDays('2026-08-20', '2026-08-20', 0.25, 'AM');
            $this->fail('Arbitrary fractional unit was accepted.');
        } catch (InvalidArgumentException) {
        }

        $this->expectException(InvalidArgumentException::class);
        $calendar->calculateLeaveDays('2026-08-20', '2026-08-20', 0.5, 'NOON');
    }

    public function test_business_date_normalization_is_explicitly_asia_manila_without_changing_date_only_values(): void
    {
        $calendar = app(WorkingCalendarService::class);
        $normalized = $calendar->normalizeBusinessDate('2026-08-14');

        $this->assertSame('Asia/Manila', $normalized->getTimezone()->getName());
        $this->assertSame('2026-08-14', $normalized->toDateString());
        $this->assertSame(0.0, $calendar->calculateLeaveUnits('2026-08-15', '2026-08-15'));
    }
}
