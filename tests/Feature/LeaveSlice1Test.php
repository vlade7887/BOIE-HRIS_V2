<?php

namespace Tests\Feature;

use App\Models\Base;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeClass;
use App\Models\EmploymentStatus;
use App\Models\LeaveEntitlement;
use App\Models\LeaveEntitlementCycle;
use App\Models\LeaveType;
use App\Models\Position;
use App\Models\Section;
use App\Models\Unit;
use App\Models\User;
use App\Services\LeaveEntitlementService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaveSlice1Test extends TestCase
{
    use RefreshDatabase;

    public function test_leave_type_seeder_creates_configurable_defaults_and_is_idempotent(): void
    {
        $this->seed();
        $this->seed();

        $this->assertDatabaseCount('leave_types', 3);
        $this->assertDatabaseHas('leave_types', ['code' => 'VL', 'annual_entitlement_days' => 15]);
        $this->assertDatabaseHas('leave_types', ['code' => 'SL', 'annual_entitlement_days' => 15]);
        $this->assertDatabaseHas('leave_types', ['code' => 'EL', 'annual_entitlement_days' => 10]);
    }

    public function test_missing_regularization_date_or_ineligible_employee_receives_no_cycle(): void
    {
        $this->seed();
        $service = app(LeaveEntitlementService::class);
        $missingDate = $this->employee();
        $inactive = $this->employee(['is_active' => false, 'date_regularized' => '2020-01-01']);
        $archived = $this->employee(['date_regularized' => '2020-01-01']);
        $archived->delete();

        $this->assertNull($service->applicableCycle($missingDate, '2026-08-14'));
        $this->assertNull($service->applicableCycle($inactive, '2026-08-14'));
        $this->assertNull($service->applicableCycle($archived, '2026-08-14'));
        $this->assertDatabaseCount('leave_entitlement_cycles', 0);
    }

    public function test_cycle_is_created_only_on_or_after_the_first_anniversary_and_boundaries_are_correct(): void
    {
        $this->seed();
        $employee = $this->employee(['date_regularized' => '2025-07-10']);
        $service = app(LeaveEntitlementService::class);

        $this->assertNull($service->applicableCycle($employee, '2026-07-09'));
        $cycle = $service->applicableCycle($employee, '2026-07-10');

        $this->assertSame('2026-07-10', $cycle->cycle_start_date->toDateString());
        $this->assertSame('2027-07-09', $cycle->cycle_end_date->toDateString());
        $this->assertSame($cycle->id, $service->applicableCycle($employee, '2027-02-01')->id);
    }

    public function test_next_anniversary_creates_a_new_cycle_and_one_entitlement_per_active_type(): void
    {
        $this->seed();
        $employee = $this->employee(['date_regularized' => '2025-07-10']);
        $service = app(LeaveEntitlementService::class);

        $first = $service->applicableCycle($employee, '2026-07-10');
        $second = $service->applicableCycle($employee, '2027-07-10');

        $this->assertNotSame($first->id, $second->id);
        $this->assertCount(3, $first->entitlements);
        $this->assertCount(3, $second->entitlements);
        $this->assertDatabaseHas('leave_entitlements', ['leave_entitlement_cycle_id' => $first->id, 'granted_days' => 15]);
        $this->assertDatabaseHas('leave_entitlements', ['leave_entitlement_cycle_id' => $first->id, 'granted_days' => 10]);
    }

    public function test_repeated_cycle_lookup_is_idempotent_and_snapshots_configuration_changes(): void
    {
        $this->seed();
        $employee = $this->employee(['date_regularized' => '2025-07-10']);
        $service = app(LeaveEntitlementService::class);
        $cycle = $service->applicableCycle($employee, '2026-07-10');
        $vl = LeaveType::where('code', 'VL')->firstOrFail();
        $entitlement = LeaveEntitlement::where('leave_entitlement_cycle_id', $cycle->id)->where('leave_type_id', $vl->id)->firstOrFail();

        $vl->update(['annual_entitlement_days' => 20]);
        $sameCycle = $service->applicableCycle($employee, '2026-07-10');

        $this->assertSame($cycle->id, $sameCycle->id);
        $this->assertSame('15.00', (string) $entitlement->fresh()->granted_days);
        $this->assertSame(1, LeaveEntitlementCycle::count());
        $this->assertSame(3, LeaveEntitlement::where('leave_entitlement_cycle_id', $cycle->id)->count());
    }

    public function test_half_day_compatible_precision_is_supported(): void
    {
        $this->seed();
        $leaveType = LeaveType::create(['code' => 'SPECIAL', 'name' => 'Special Leave', 'annual_entitlement_days' => 0.5, 'allows_half_day' => true]);
        $employee = $this->employee(['date_regularized' => '2025-07-10']);
        $cycle = app(LeaveEntitlementService::class)->applicableCycle($employee, '2026-07-10');

        $this->assertSame('0.50', (string) $cycle->entitlements()->where('leave_type_id', $leaveType->id)->value('granted_days'));
    }

    public function test_leave_type_crud_archives_and_restores_without_a_delete_route(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->post(route('leave-types.store'), [
            'code' => ' vl ', 'name' => 'Vacation Leave', 'annual_entitlement_days' => '15.0', 'is_active' => true,
        ]);

        $response->assertRedirect(route('leave-types.index'));
        $leaveType = LeaveType::firstOrFail();
        $this->assertSame('VL', $leaveType->code);
        $this->actingAs($user)->post(route('leave-types.archive', $leaveType))->assertRedirect();
        $this->assertSoftDeleted('leave_types', ['id' => $leaveType->id]);
        $this->actingAs($user)->post(route('leave-types.restore', $leaveType->id))->assertRedirect();
        $this->assertDatabaseHas('leave_types', ['id' => $leaveType->id, 'deleted_at' => null]);
        $this->assertFalse(collect(app('router')->getRoutes())->contains(fn ($route) => $route->getName() === 'leave-types.destroy'));
    }

    public function test_existing_vl_can_be_updated_while_retaining_its_code(): void
    {
        $this->seed();
        $user = User::factory()->create();
        $vl = LeaveType::where('code', 'VL')->firstOrFail();

        $response = $this->actingAs($user)->put(route('leave-types.update', $vl), [
            'code' => 'VL',
            'name' => 'Vacation Leave Updated',
            'annual_entitlement_days' => 15,
            'allows_half_day' => true,
            'is_active' => true,
        ]);

        $response->assertRedirect(route('leave-types.index'));
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('leave_types', ['id' => $vl->id, 'code' => 'VL', 'name' => 'Vacation Leave Updated']);
    }

    public function test_leave_type_cannot_be_changed_to_an_existing_code(): void
    {
        $this->seed();
        $user = User::factory()->create();
        $vl = LeaveType::where('code', 'VL')->firstOrFail();

        $response = $this->actingAs($user)->put(route('leave-types.update', $vl), [
            'code' => 'SL',
            'name' => $vl->name,
            'annual_entitlement_days' => 15,
            'allows_half_day' => true,
            'is_active' => true,
        ]);

        $response->assertSessionHasErrors(['code']);
        $this->assertDatabaseHas('leave_types', ['id' => $vl->id, 'code' => 'VL']);
    }

    public function test_description_can_be_updated_without_changing_leave_type_code(): void
    {
        $this->seed();
        $user = User::factory()->create();
        $vl = LeaveType::where('code', 'VL')->firstOrFail();

        $response = $this->actingAs($user)->put(route('leave-types.update', $vl), [
            'code' => $vl->code,
            'name' => $vl->name,
            'description' => 'Updated description',
            'annual_entitlement_days' => $vl->annual_entitlement_days,
            'allows_half_day' => true,
            'is_active' => true,
        ]);

        $response->assertRedirect(route('leave-types.index'));
        $this->assertDatabaseHas('leave_types', ['id' => $vl->id, 'code' => 'VL', 'description' => 'Updated description']);
    }

    public function test_archived_leave_type_code_remains_unique(): void
    {
        $this->seed();
        $user = User::factory()->create();
        $vl = LeaveType::where('code', 'VL')->firstOrFail();
        $this->actingAs($user)->post(route('leave-types.archive', $vl));

        $response = $this->actingAs($user)->post(route('leave-types.store'), [
            'code' => 'VL',
            'name' => 'Another Vacation Leave',
            'annual_entitlement_days' => 15,
        ]);

        $response->assertSessionHasErrors(['code']);
        $this->assertDatabaseCount('leave_types', 3);
    }

    public function test_database_uniqueness_guards_cycles(): void
    {
        $this->seed();
        $employee = $this->employee(['date_regularized' => '2025-07-10']);
        $cycle = LeaveEntitlementCycle::create(['employee_id' => $employee->id, 'cycle_start_date' => '2026-07-10', 'cycle_end_date' => '2027-07-09']);
        $this->expectException(QueryException::class);
        LeaveEntitlementCycle::create(['employee_id' => $employee->id, 'cycle_start_date' => '2026-07-10', 'cycle_end_date' => '2027-07-09']);
    }

    public function test_database_uniqueness_guards_entitlements(): void
    {
        $this->seed();
        $employee = $this->employee(['date_regularized' => '2025-07-10']);
        $cycle = LeaveEntitlementCycle::create(['employee_id' => $employee->id, 'cycle_start_date' => '2026-07-10', 'cycle_end_date' => '2027-07-09']);
        $leaveType = LeaveType::firstOrFail();
        LeaveEntitlement::create(['leave_entitlement_cycle_id' => $cycle->id, 'leave_type_id' => $leaveType->id, 'granted_days' => 15]);

        $this->expectException(QueryException::class);
        LeaveEntitlement::create(['leave_entitlement_cycle_id' => $cycle->id, 'leave_type_id' => $leaveType->id, 'granted_days' => 15]);
    }

    private function employee(array $overrides = []): Employee
    {
        static $sequence = 0;
        $sequence++;
        $company = Company::firstOrCreate(['company_code' => 'C' . $sequence], ['company_name' => 'Company ' . $sequence]);
        $base = Base::firstOrCreate(['base_code' => 'B' . $sequence], ['base_name' => 'Base ' . $sequence]);
        $unit = Unit::firstOrCreate(['unit_code' => 'U' . $sequence], ['base_id' => $base->id, 'unit_name' => 'Unit ' . $sequence]);
        $department = Department::firstOrCreate(['department_code' => 'D' . $sequence], ['unit_id' => $unit->id, 'department_name' => 'Department ' . $sequence]);
        $section = Section::firstOrCreate(['section_code' => 'S' . $sequence], ['department_id' => $department->id, 'section_name' => 'Section ' . $sequence]);
        $position = Position::firstOrCreate(['position_code' => 'P' . $sequence], ['section_id' => $section->id, 'position_name' => 'Position ' . $sequence]);
        $status = EmploymentStatus::firstOrCreate(['status_code' => 'ES' . $sequence], ['status_name' => 'Regular']);
        $class = EmployeeClass::firstOrCreate(['class_code' => 'EC' . $sequence], ['class_name' => 'Rank and File']);

        return Employee::create(array_merge([
            'employee_no' => 'EMP-' . $sequence, 'last_name' => 'Doe', 'first_name' => 'Jane', 'gender' => 'Female', 'civil_status' => 'Single', 'birth_date' => '1990-01-01',
            'company_id' => $company->id, 'base_id' => $base->id, 'unit_id' => $unit->id, 'department_id' => $department->id, 'section_id' => $section->id, 'position_id' => $position->id,
            'employment_status_id' => $status->id, 'employee_class_id' => $class->id, 'date_hired' => '2020-01-01', 'is_active' => true,
        ], $overrides));
    }
}
