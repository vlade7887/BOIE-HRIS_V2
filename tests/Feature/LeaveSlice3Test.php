<?php

namespace Tests\Feature;

use App\Models\Base;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeClass;
use App\Models\EmploymentStatus;
use App\Models\LeaveBalanceReservation;
use App\Models\LeaveEntitlement;
use App\Models\LeaveEntitlementCycle;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Position;
use App\Models\Section;
use App\Models\Unit;
use App\Models\User;
use App\Services\LeaveBalanceReservationService;
use App\Services\LeaveFilingValidationService;
use App\Services\LeaveRequestService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class LeaveSlice3Test extends TestCase
{
    use RefreshDatabase;

    public function test_policy_seeding_is_configurable_and_idempotent(): void
    {
        $this->seed();
        $this->seed();

        $this->assertDatabaseHas('leave_types', ['code' => 'VL', 'filing_timing' => 'advance', 'minimum_advance_days' => 3, 'carryover_policy' => 'grace_period', 'carryover_grace_days' => 3]);
        $this->assertDatabaseHas('leave_types', ['code' => 'SL', 'filing_timing' => 'after_return']);
        $this->assertDatabaseHas('leave_types', ['code' => 'EL', 'filing_timing' => 'same_day']);
        $this->assertDatabaseCount('leave_types', 3);
    }

    public function test_forward_policy_backfill_repairs_existing_seeded_rows_without_overwriting_custom_types(): void
    {
        $this->seed();
        $custom = LeaveType::create(['code' => 'CUSTOM', 'name' => 'Custom Leave', 'annual_entitlement_days' => 5, 'filing_timing' => 'same_day', 'minimum_advance_days' => 0]);
        LeaveType::whereIn('code', ['VL', 'SL', 'EL'])->update(['filing_timing' => 'same_day', 'minimum_advance_days' => 0]);

        $migration = require base_path('database/migrations/2026_08_19_000042_backfill_seeded_leave_type_filing_policies.php');
        $migration->up();

        $this->assertDatabaseHas('leave_types', ['code' => 'VL', 'filing_timing' => 'advance', 'minimum_advance_days' => 3]);
        $this->assertDatabaseHas('leave_types', ['code' => 'SL', 'filing_timing' => 'after_return', 'minimum_advance_days' => 0]);
        $this->assertDatabaseHas('leave_types', ['code' => 'EL', 'filing_timing' => 'same_day', 'minimum_advance_days' => 0]);
        $this->assertSame('same_day', $custom->fresh()->filing_timing);
        $this->assertSame(0, $custom->fresh()->minimum_advance_days);
    }

    public function test_filing_validation_enforces_eligibility_and_timing_rules(): void
    {
        $this->seed();
        $service = app(LeaveFilingValidationService::class);
        $vl = LeaveType::where('code', 'VL')->firstOrFail();
        $sl = LeaveType::where('code', 'SL')->firstOrFail();
        $el = LeaveType::where('code', 'EL')->firstOrFail();
        $employee = $this->employee(['date_regularized' => '2025-01-01']);
        $filingDate = CarbonImmutable::parse('2026-08-17', 'Asia/Manila');

        $service->validate($employee, $vl, ['start_date' => '2026-08-20', 'end_date' => '2026-08-20'], $filingDate);
        $this->expectException(ValidationException::class);
        $service->validate($employee, $vl, ['start_date' => '2026-08-20', 'end_date' => '2026-08-20'], $filingDate->addDay());
    }

    public function test_vl_advance_rule_rejects_august_18_19_and_20_but_accepts_august_17(): void
    {
        $this->seed();
        $service = app(LeaveFilingValidationService::class);
        $employee = $this->employee(['date_regularized' => '2025-01-01']);
        $vl = LeaveType::where('code', 'VL')->firstOrFail();

        $service->validate($employee, $vl, ['start_date' => '2026-08-20', 'end_date' => '2026-08-20'], CarbonImmutable::parse('2026-08-17', 'Asia/Manila'));

        foreach (['2026-08-18', '2026-08-19', '2026-08-20'] as $date) {
            try {
                $service->validate($employee, $vl, ['start_date' => '2026-08-20', 'end_date' => '2026-08-20'], CarbonImmutable::parse($date, 'Asia/Manila'));
                $this->fail("VL filing on {$date} was accepted.");
            } catch (ValidationException) {
                $this->addToAssertionCount(1);
            }
        }
    }

    public function test_preview_route_enforces_persisted_vl_policy_on_the_business_date(): void
    {
        $this->seed();
        $employee = $this->employee(['date_regularized' => '2025-01-01']);
        $user = User::factory()->create();
        DB::table('employees')->where('id', $employee->id)->update(['user_id' => $user->id]);
        $vl = LeaveType::where('code', 'VL')->firstOrFail();
        $vl->update(['filing_timing' => 'advance', 'minimum_advance_days' => 3]);
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-08-19 10:00:00', 'Asia/Manila'));

        try {
            $response = $this->actingAs($user)->post(route('leave-requests.preview'), [
                'leave_type_id' => $vl->id,
                'start_date' => '2026-08-20',
                'end_date' => '2026-08-20',
                'requested_unit' => 1,
            ]);

            $response->assertSessionHasErrors('start_date');
            $this->assertDatabaseCount('approval_requests', 0);
        } finally {
            CarbonImmutable::setTestNow();
        }
    }

    public function test_sl_and_el_timing_and_zero_working_day_rules_are_enforced(): void
    {
        $this->seed();
        $service = app(LeaveFilingValidationService::class);
        $employee = $this->employee(['date_regularized' => '2025-01-01']);
        $sl = LeaveType::where('code', 'SL')->firstOrFail();
        $el = LeaveType::where('code', 'EL')->firstOrFail();
        $filingDate = CarbonImmutable::parse('2026-08-24', 'Asia/Manila');

        $service->validate($employee, $sl, ['start_date' => '2026-08-20', 'end_date' => '2026-08-21', 'returned_to_work_date' => '2026-08-24'], $filingDate);
        $service->validate($employee, $el, ['start_date' => '2026-08-24', 'end_date' => '2026-08-24'], $filingDate);

        try {
            $service->validate($employee, $sl, ['start_date' => '2026-08-20', 'end_date' => '2026-08-21', 'returned_to_work_date' => '2026-08-22'], CarbonImmutable::parse('2026-08-21', 'Asia/Manila'));
            $this->fail('Same-day Sick Leave filing was accepted.');
        } catch (ValidationException) {
        }

        $this->expectException(ValidationException::class);
        $service->validate($employee, $el, ['start_date' => '2026-08-22', 'end_date' => '2026-08-23'], $filingDate);
    }

    public function test_half_day_is_single_date_and_snapshot_draft_persists_calendar_rows(): void
    {
        $this->seed();
        $employee = $this->employee(['date_regularized' => '2025-01-01']);
        $holiday = \App\Models\Holiday::create(['holiday_date' => '2026-08-25', 'name' => 'Snapshot Holiday']);
        $service = app(LeaveRequestService::class);
        $vl = LeaveType::where('code', 'VL')->firstOrFail();

        $draft = $service->saveDraft($employee, ['leave_type_id' => $vl->id, 'start_date' => '2026-08-24', 'end_date' => '2026-08-26', 'requested_unit' => 1, 'reason' => 'Rest']);
        $this->assertSame(2, $draft->days()->where('is_working_day', true)->count());
        $this->assertSame(3, $draft->days()->count());
        $this->assertSame($holiday->id, $draft->days()->whereDate('leave_date', '2026-08-25')->value('holiday_id'));
        $this->assertSame('Snapshot Holiday', $draft->days()->whereDate('leave_date', '2026-08-25')->value('holiday_name_snapshot'));

        $holiday->update(['name' => 'Changed Holiday']);
        $this->assertSame('Snapshot Holiday', $draft->days()->whereDate('leave_date', '2026-08-25')->value('holiday_name_snapshot'));

        $this->expectException(ValidationException::class);
        app(LeaveFilingValidationService::class)->validate($employee, $vl, ['start_date' => '2026-08-24', 'end_date' => '2026-08-25', 'requested_unit' => 0.5, 'half_day_period' => 'AM'], CarbonImmutable::parse('2026-08-17', 'Asia/Manila'));
    }

    public function test_overlap_statuses_and_same_day_half_day_policy(): void
    {
        $this->seed();
        $employee = $this->employee(['date_regularized' => '2025-01-01']);
        $type = LeaveType::where('code', 'VL')->firstOrFail();
        $make = fn (string $status) => LeaveRequest::create(['employee_id' => $employee->id, 'leave_type_id' => $type->id, 'leave_type_code_snapshot' => 'VL', 'leave_type_name_snapshot' => 'Vacation Leave', 'start_date' => '2026-08-20', 'end_date' => '2026-08-20', 'total_units' => 0.5, 'status' => $status]);
        $make(LeaveRequest::STATUS_DRAFT);
        $this->assertFalse(app(\App\Services\LeaveOverlapService::class)->hasBlockingOverlap($employee->id, '2026-08-20', '2026-08-20'));
        $make(LeaveRequest::STATUS_REJECTED);
        $make(LeaveRequest::STATUS_CANCELLED);
        $this->assertFalse(app(\App\Services\LeaveOverlapService::class)->hasBlockingOverlap($employee->id, '2026-08-20', '2026-08-20'));
        $make(LeaveRequest::STATUS_PENDING);
        $this->assertTrue(app(\App\Services\LeaveOverlapService::class)->hasBlockingOverlap($employee->id, '2026-08-20', '2026-08-20'));
    }

    public function test_vl_reservation_allocates_old_balance_before_new_balance_and_is_idempotent(): void
    {
        $this->seed();
        $employee = $this->employee(['date_regularized' => '2024-07-10']);
        $type = LeaveType::where('code', 'VL')->firstOrFail();
        $entitlements = app(\App\Services\LeaveEntitlementService::class);
        $oldCycle = $entitlements->applicableCycle($employee, '2025-07-10');
        $newCycle = $entitlements->applicableCycle($employee, '2026-07-10');
        $old = $oldCycle->entitlements()->where('leave_type_id', $type->id)->firstOrFail();
        $new = $newCycle->entitlements()->where('leave_type_id', $type->id)->firstOrFail();
        $old->update(['granted_days' => 2]);
        $request = $this->pendingRequest($employee, $type, '2026-08-20', 3);

        $reservations = app(LeaveBalanceReservationService::class)->reserve($request);
        $this->assertCount(2, $reservations);
        $this->assertSame('2.00', (string) $reservations->firstWhere('leave_entitlement_id', $old->id)->reserved_days);
        $this->assertSame('1.00', (string) $reservations->firstWhere('leave_entitlement_id', $new->id)->reserved_days);
        $this->assertSame('2.00', (string) $old->fresh()->reserved_days);
        $this->assertSame('1.00', (string) $new->fresh()->reserved_days);
        $this->assertCount(2, app(LeaveBalanceReservationService::class)->reserve($request));
        $this->assertDatabaseCount('leave_balance_reservations', 2);
    }

    public function test_expired_old_vl_is_ignored_and_insufficient_reservation_is_atomic(): void
    {
        $this->seed();
        $employee = $this->employee(['date_regularized' => '2024-07-10']);
        $type = LeaveType::where('code', 'VL')->firstOrFail();
        $service = app(\App\Services\LeaveEntitlementService::class);
        $oldCycle = $service->applicableCycle($employee, '2025-07-10');
        $newCycle = $service->applicableCycle($employee, '2026-07-10');
        $old = $oldCycle->entitlements()->where('leave_type_id', $type->id)->firstOrFail();
        $new = $newCycle->entitlements()->where('leave_type_id', $type->id)->firstOrFail();
        $old->update(['granted_days' => 10, 'expired_days' => 10]);
        $new->update(['granted_days' => 1]);
        $request = $this->pendingRequest($employee, $type, '2026-12-20', 2);

        try {
            app(LeaveBalanceReservationService::class)->reserve($request);
            $this->fail('Insufficient balance was accepted.');
        } catch (ValidationException) {
        }

        $this->assertDatabaseCount('leave_balance_reservations', 0);
        $this->assertSame('0.00', (string) $old->fresh()->reserved_days);
        $this->assertSame('0.00', (string) $new->fresh()->reserved_days);
    }

    public function test_sl_and_el_do_not_allocate_from_previous_cycle(): void
    {
        $this->seed();
        $employee = $this->employee(['date_regularized' => '2024-07-10']);
        $cycleService = app(\App\Services\LeaveEntitlementService::class);
        $oldCycle = $cycleService->applicableCycle($employee, '2025-07-10');
        $newCycle = $cycleService->applicableCycle($employee, '2026-07-10');

        foreach (['SL', 'EL'] as $index => $code) {
            $type = LeaveType::where('code', $code)->firstOrFail();
            $old = $oldCycle->entitlements()->where('leave_type_id', $type->id)->firstOrFail();
            $new = $newCycle->entitlements()->where('leave_type_id', $type->id)->firstOrFail();
            $old->update(['granted_days' => 10]);
            $date = $index === 0 ? '2026-08-24' : '2026-08-26';
            $request = $this->pendingRequest($employee, $type, $date, 1);
            $request->update(['start_date' => $date, 'end_date' => $date]);
            app(LeaveBalanceReservationService::class)->reserve($request);
            $this->assertSame('0.00', (string) $old->fresh()->reserved_days);
            $this->assertSame('1.00', (string) $new->fresh()->reserved_days);
        }
    }

    public function test_draft_ui_does_not_create_approval_request_or_pending_user_flow(): void
    {
        $this->seed();
        $employee = $this->employee(['date_regularized' => '2025-01-01']);
        $user = User::factory()->create();
        DB::table('employees')->where('id', $employee->id)->update(['user_id' => $user->id]);
        $type = LeaveType::where('code', 'VL')->firstOrFail();

        $response = $this->actingAs($user)->post(route('leave-requests.drafts.store'), ['leave_type_id' => $type->id, 'start_date' => '2026-08-24', 'end_date' => '2026-08-24', 'requested_unit' => 1, 'reason' => 'Draft']);
        $response->assertRedirect(route('leave-requests.create'));
        $this->assertDatabaseHas('leave_requests', ['employee_id' => $employee->id, 'status' => LeaveRequest::STATUS_DRAFT]);
        $this->assertDatabaseCount('approval_requests', 0);
    }

    public function test_initial_leave_create_page_renders_with_default_form_values(): void
    {
        $this->seed();
        $employee = $this->employee(['date_regularized' => '2025-01-01']);
        $user = User::factory()->create();
        DB::table('employees')->where('id', $employee->id)->update(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('leave-requests.create'));

        $response->assertOk()
            ->assertSee('Leave details')
            ->assertSee('Choose...')
            ->assertSee('Full day')
            ->assertDontSee('Preview ready');
    }

    public function test_leave_preview_still_renders_calculated_values(): void
    {
        $this->seed();
        $employee = $this->employee(['date_regularized' => '2025-01-01']);
        $user = User::factory()->create();
        DB::table('employees')->where('id', $employee->id)->update(['user_id' => $user->id]);
        $type = LeaveType::where('code', 'VL')->firstOrFail();

        $response = $this->actingAs($user)->post(route('leave-requests.preview'), [
            'leave_type_id' => $type->id,
            'start_date' => '2026-08-24',
            'end_date' => '2026-08-24',
            'requested_unit' => 1,
        ]);

        $response->assertOk()
            ->assertSee('Preview ready.')
            ->assertSee('1.00 Leave unit(s) counted.')
            ->assertSee('2026-08-24');
    }

    public function test_get_preview_fallback_redirects_safely_to_create_without_business_action(): void
    {
        $this->seed();
        $employee = $this->employee(['date_regularized' => '2025-01-01']);
        $user = User::factory()->create();
        DB::table('employees')->where('id', $employee->id)->update(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('leave-requests.preview'));

        $response->assertRedirect(route('leave-requests.create'));
        $this->assertDatabaseCount('leave_requests', 0);
        $this->assertDatabaseCount('approval_requests', 0);
    }

    private function pendingRequest(Employee $employee, LeaveType $type, string $date, float $units): LeaveRequest
    {
        return LeaveRequest::create([
            'employee_id' => $employee->id,
            'leave_type_id' => $type->id,
            'leave_type_code_snapshot' => $type->code,
            'leave_type_name_snapshot' => $type->name,
            'start_date' => $date,
            'end_date' => $date,
            'total_units' => $units,
            'status' => LeaveRequest::STATUS_PENDING,
        ]);
    }

    private function employee(array $overrides = []): Employee
    {
        static $sequence = 1000;
        $sequence++;
        $company = Company::create(['company_code' => 'C'.$sequence, 'company_name' => 'Company '.$sequence]);
        $base = Base::create(['base_code' => 'B'.$sequence, 'base_name' => 'Base '.$sequence]);
        $unit = Unit::create(['unit_code' => 'U'.$sequence, 'base_id' => $base->id, 'unit_name' => 'Unit '.$sequence]);
        $department = Department::create(['department_code' => 'D'.$sequence, 'unit_id' => $unit->id, 'department_name' => 'Department '.$sequence]);
        $section = Section::create(['section_code' => 'S'.$sequence, 'department_id' => $department->id, 'section_name' => 'Section '.$sequence]);
        $position = Position::create(['position_code' => 'P'.$sequence, 'section_id' => $section->id, 'position_name' => 'Position '.$sequence]);
        $status = EmploymentStatus::create(['status_code' => 'ES'.$sequence, 'status_name' => 'Regular']);
        $class = EmployeeClass::create(['class_code' => 'EC'.$sequence, 'class_name' => 'Rank and File']);

        return Employee::create(array_merge([
            'employee_no' => 'EMP-'.$sequence, 'last_name' => 'Doe', 'first_name' => 'Jane', 'gender' => 'Female', 'civil_status' => 'Single', 'birth_date' => '1990-01-01',
            'company_id' => $company->id, 'base_id' => $base->id, 'unit_id' => $unit->id, 'department_id' => $department->id, 'section_id' => $section->id, 'position_id' => $position->id,
            'employment_status_id' => $status->id, 'employee_class_id' => $class->id, 'date_hired' => '2020-01-01', 'is_active' => true,
        ], $overrides));
    }
}
