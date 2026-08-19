# BOIE HRIS Leave Module Architecture

## Slice 3 implementation note — 2026-08-19

Leave Slice 3 is implemented. Working-calendar dates are interpreted explicitly in `Asia/Manila`; system timestamps and the application-wide UTC configuration remain unchanged. Philippine holidays are database-configured through the Holiday master data; no assumed annual holiday list is seeded. Leave draft day rows persist the computed calendar result; future submitted Leave requests must retain those immutable snapshots instead of recalculating history after Holiday master-data edits.

Status: Approved architecture; Slices 1–3 implemented, Slice 4 atomic Approval Engine submission is next

Date: 2026-08-14

This document records the approved Leave policies and implementation boundary. Slice 1 implements Leave types and anniversary entitlements, Slice 2 implements the working calendar, and Slice 3 implements the Leave filing/reservation foundation. The repository terminology remains `approvable_type`/`approvable_id`; atomic Leave filing with Approval Engine integration is deferred to Slice 4.

## 1. Scope and architecture boundary

Leave owns:

- Configurable leave types and leave-specific configuration.
- Anniversary-based eligibility, entitlements, balances, reservations, consumption, expiry, and payout-interface records.
- Leave dates, half-day units, reason, leave-specific validation, and working-day calculation.
- Immutable Leave request and calculated-day snapshots.
- Leave-specific overlap, timing, balance, and cancellation rules.

The generic Approval Engine owns:

- Employee-selected ordered approvers.
- Automatic HR final approval.
- Immutable approval-route snapshots.
- Sequential approve, reject, and cancel runtime behavior.
- Existing delegation resolution through `ApprovalDelegationResolver` and the runtime services.
- Approval action history, idempotency, and audit logging.

Leave must implement the existing generic approvable/request contract with `module_key = leave`. It must not duplicate approval routing, delegation, action history, or audit behavior. Delegation is already implemented and is required from the first Leave slice.

## 2. Approved Leave policies

### Eligibility cycle

Entitlement is based on the employee's regularization anniversary, not the calendar year. HR must populate `employees.date_regularized`. If it is missing, no automatic entitlement is created and the employee cannot file credit-based Leave. Eligibility begins on the first regularization anniversary.

Example: an employee hired and regularized on July 10, 2025 receives the first entitlement beginning July 10, 2026.

### Annual entitlement

The initial configured entitlements are:

| Leave type | Annual entitlement |
| --- | ---: |
| Vacation Leave | 15 days |
| Sick Leave | 15 days |
| Emergency Leave | 10 days |

Leave types and entitlement values must remain master-data/configuration driven where practical. Business rules must not hard-code these values in controllers or generic Approval Engine code.

### Anniversary treatment

Vacation Leave receives a new 15-day entitlement at each anniversary. Unused previous-cycle VL remains usable for three months after the anniversary, and expires after that grace period. During the grace period, the old/carryover entitlement is consumed before the new-cycle entitlement.

Sick Leave is not carried forward. Remaining previous-cycle SL is recorded for future company payout and exposed through a clean payout/interface record for Payroll. New 15-day SL entitlement begins at the anniversary. Payroll processing is outside this Leave phase.

Emergency Leave expires at the anniversary, has no carryover and no cash payout, and receives a new 10-day entitlement for the new cycle.

### Reservation lifecycle

- Draft: no reservation.
- Submitted/Pending: reserve the required credits.
- Rejected: release the reservation.
- Cancelled: release the reservation.
- Approved: convert reserved credits to consumed credits.

Reservation must be atomic and concurrency-safe. Insufficient balance blocks filing completely; excess days are not converted automatically to Leave Without Pay.

### Overlap and draft behavior

Pending and Approved requests block overlapping filing for the same employee. Rejected and Cancelled requests do not block a new request. Draft requests do not reserve credits and do not block overlap because they are not submitted requests. Submission must revalidate dates, overlap, eligibility, timing, and balance inside the same transaction that creates the reservation. A draft may therefore fail at submission if another request was submitted first.

### Filing timing

- Vacation Leave must be submitted at least three days before the first Leave date. Approval completion does not need to occur three days before; only filing/submission timing is required.
- Sick Leave is filed after the employee returns to work.
- Emergency Leave allows same-day filing.

### Units and calendar

V1 supports full-day `1.0` and half-day `0.5` units. Every half-day records AM or PM. Hourly Leave and arbitrary fractional units are excluded.

Weekends and configured Philippine public holidays are excluded through one centralized HRIS working-calendar service. Controllers and Approval Engine services must not calculate working days themselves. Leave stores calculated request-day snapshots, including the applicable calendar decision, so later calendar changes cannot silently rewrite historical approved requests.

### Department snapshot and cancellation

A submitted request stores one immutable request department snapshot. Employee transfers after submission must not change its approval or delegation department context.

The requester may cancel their own request only while it is Pending. After final approval, ordinary employee cancellation is prohibited. HR-controlled reversal/cancellation is future work and is not part of the first Leave slice.

### Attachments

Attachments are optional in v1. Leave types should be able to support future configuration such as `requires_attachment = true`, but document storage must not block the first implementation.

## 3. Proposed schema

The proposal follows the repository's Laravel conventions: singular model names, plural snake_case tables, `*_id` foreign keys, timestamps, foreign keys, and soft deletes only where archive behavior is appropriate. Slice 3 implements Leave request, day, reservation, and ledger foundation tables; payout and rollover tables/processors remain future work.

### `leave_types`

Important columns:

- `id`
- `code` and `name`
- `description`
- `is_credit_based`
- `annual_entitlement_days` or an equivalent configurable entitlement value
- `filing_rule` (`advance_days`, `after_return`, or `same_day`)
- `carryover_policy` (`grace_period`, `payout`, or `expire`)
- `carryover_grace_days`
- `requires_attachment` (default `false`)
- `status` (`active`, `inactive`, `archived`)
- `created_at`, `updated_at`, `deleted_at`

Relationships: has many entitlement and request records.

Indexes/uniqueness: unique `code`; index `status`.

Retention: archive with soft delete; historical requests retain their immutable `leave_type_code` and `leave_type_name` snapshots so a master-data archive does not erase history.

### `leave_entitlement_cycles`

One row represents one employee anniversary cycle.

Important columns:

- `id`
- `employee_id`
- `cycle_start_date`, `cycle_end_date`
- `anniversary_date`
- `date_regularized_snapshot`
- `status` (`open`, `grace`, `closed`)
- `opened_at`, `closed_at`
- timestamps

Relationships: belongs to `employees`; has many entitlements and payout records.

Indexes/uniqueness: unique `employee_id + cycle_start_date`; index `employee_id + status`; index `anniversary_date`.

Retention: do not soft-delete or physically delete financial/credit history. Close cycles and retain them.

Immutable fields: employee identity and the regularization/cycle date snapshots cannot change after creation.

### `leave_entitlements`

One row represents a leave-type allocation within an entitlement cycle.

Important columns:

- `id`
- `leave_entitlement_cycle_id`
- `leave_type_id`
- `leave_type_code_snapshot`, `leave_type_name_snapshot`
- `entitled_units`
- `carryover_units`
- `expired_units`
- `paid_out_units`
- `consumed_units`
- `reserved_units`
- `available_units` or a consistently derived equivalent
- `status` (`open`, `expired`, `closed`)
- timestamps

Relationships: belongs to a cycle and leave type; has reservations and ledger entries.

Indexes/uniqueness: unique `leave_entitlement_cycle_id + leave_type_id`; index employee access through the cycle; index `status`.

Retention: retain permanently as credit history; close rather than delete.

Immutable fields: cycle identity and leave-type snapshots remain fixed. Mutable totals are changed only through transactional ledger/reservation operations.

### `leave_requests`

This is the Leave approvable record consumed by the generic Approval Engine through `approval_requests.approvable_type/approvable_id`.

Important columns:

- `id`
- `employee_id`
- `leave_type_id`
- `leave_type_code_snapshot`, `leave_type_name_snapshot`
- `request_department_id` and `request_department_name_snapshot`
- `date_from`, `date_to`
- `total_units`
- `reason`
- `status` (`draft`, `pending`, `approved`, `rejected`, `cancelled`)
- `submitted_at`, `approved_at`, `rejected_at`, `cancelled_at`
- `returned_to_work_date` for Sick Leave validation
- idempotency key/correlation field for duplicate submission protection
- timestamps

Relationships: belongs to employee, leave type, and department snapshot; has request-day rows, reservations, and the generic approval request.

Indexes/uniqueness: index `employee_id + status`; index `employee_id + date_from + date_to`; index `request_department_id`; unique idempotency key per requester/module where applicable.

Retention: retain all submitted requests. Draft retention/cleanup is a later operational policy; submitted records are never hard-deleted.

Immutable snapshots: employee identity, leave type code/name, department ID/name, submitted date range, total units, reason-at-submission, and calculated-day snapshot are immutable after submission. Approval status timestamps and cancellation/rejection metadata are runtime fields.

### `leave_request_days`

One row represents one calculated working-calendar day in a submitted request.

Important columns:

- `id`
- `leave_request_id`
- `leave_date`
- `units` (`1.0` or `0.5`)
- `half_day_period` (`AM`, `PM`, or null for full day)
- `calendar_status_snapshot` (working day/holiday/weekend decision)
- `holiday_name_snapshot` nullable
- `calendar_rule_version` or equivalent calendar snapshot identifier
- timestamps

Relationships: belongs to `leave_requests`.

Indexes/uniqueness: unique `leave_request_id + leave_date`; index `leave_date` for overlap checks.

Retention: retain with the request; cascade only if an unsubmitted draft is explicitly discarded under an approved retention rule.

Immutable snapshot fields: date, units, AM/PM, holiday name, and calendar decision cannot change after submission.

### `leave_balance_reservations`

A reservation links a pending request to the entitlement units it temporarily holds.

Important columns:

- `id`
- `leave_request_id`
- `leave_entitlement_id`
- `units`
- `status` (`reserved`, `released`, `consumed`)
- `reserved_at`, `released_at`, `consumed_at`
- unique reservation/idempotency key
- timestamps

Relationships: belongs to request and entitlement.

Indexes/uniqueness: unique active reservation identity for request and entitlement; index entitlement/status; index request/status.

Retention: retain reservation history; never delete released or consumed records.

### `leave_balance_ledger`

Append-only financial/credit history for grants, carryover, expiry, payout, reservation release, and consumption.

Important columns:

- `id`
- `employee_id`
- `leave_type_id`
- `leave_entitlement_id`
- `leave_request_id` nullable
- `entry_type` (`entitlement`, `carryover`, `expiry`, `reservation`, `reservation_release`, `consumption`, `payout`)
- signed `units`
- `source_type`, `source_id`, and idempotency key
- `occurred_at`
- metadata JSON
- timestamps

Relationships: belongs to employee, leave type, entitlement, and optionally request.

Indexes/uniqueness: index employee/type/occurred_at; index entitlement/occurred_at; unique idempotency key per source operation.

Retention: append-only; no updates or deletes. Corrections are compensating entries.

### `leave_sl_payouts`

Interface record for Payroll to consume later; this Leave phase does not perform payroll processing.

Important columns:

- `id`
- `employee_id`
- `leave_entitlement_cycle_id`
- `leave_entitlement_id`
- `units`
- `status` (`pending_payroll`, `exported`, `processed`, `voided`)
- `payroll_reference` nullable
- `exported_at`, `processed_at`
- immutable source/correlation key
- timestamps

Relationships: belongs to employee, cycle, and SL entitlement.

Indexes/uniqueness: unique `leave_entitlement_id` for the payout record; index employee/status; index payroll status.

Retention: retain as financial interface history. Voiding requires a compensating/audited action; no physical deletion.

## 4. Transaction, locking, and audit rules

- Entitlement creation locks the employee's anniversary/cycle key and uses a unique cycle constraint so retries cannot create duplicate cycles.
- Anniversary rollover runs transactionally: create the new cycle, create configured entitlements, close/expire old balances, create the SL payout record, and write ledger entries. VL carryover is determined once and the old balance remains first in consumption order through the grace period.
- Reservation locks all applicable entitlement rows in deterministic entitlement-ID order, calculates available units, and writes reservation plus ledger records in one transaction. If total available units are insufficient, the transaction rolls back and filing is blocked.
- Approval consumption locks the request's reservations and entitlement rows, verifies the request is still pending and reservations are active, marks reservations consumed, writes consumption ledger entries, and is idempotent.
- Rejection and Pending cancellation lock active reservations, mark them released, and write release ledger entries. Repeating the same operation must not create another release.
- VL consumption selects eligible old/carryover entitlement first, then the current cycle, using deterministic ordering and row locks.
- Submission uses an idempotency key and a transaction covering request creation, calculated-day snapshots, overlap recheck, reservation, and generic Approval Engine submission. Duplicate submissions return the original result or a safe conflict.
- Overlap checks run inside the submission transaction against Pending and Approved requests for the same employee. Draft, Rejected, and Cancelled requests are excluded.
- Credit and payout history is auditable through append-only ledger, reservation, payout, and generic Approval Engine audit records.

## 5. Approval Engine integration

The Leave request implements the existing approvable contract and supplies:

- `module_key`: `leave`.
- Approval department: immutable `request_department_id` snapshot.
- Request identity: the Leave request ID and immutable Leave data.

At submission, Leave completes its own validation and reservation transaction boundary before invoking the existing generic submission flow, or coordinates both under one outer transaction if the repository service contract permits. The Engine then snapshots the selected ordered route and HR final step. On runtime outcomes, Leave listens through a service/application boundary for Approved, Rejected, and Cancelled outcomes and performs the corresponding idempotent balance operation. Delegation is resolved only by the existing generic runtime.

## 6. Safe implementation slices

1. Leave Type and anniversary entitlement foundation: configurable leave types, `date_regularized` eligibility validation, cycles, entitlements, and ledger grants.
2. Working Calendar and day computation: centralized Philippine holiday configuration/service, weekend/holiday exclusion, full-day and AM/PM half-day snapshots.
3. Leave filing and balance reservation: Leave request form/service, timing rules, overlap checks, insufficient-balance blocking, idempotency, and concurrency-safe reservations.
4. Approval Engine integration: Leave approvable adapter, immutable department snapshot, `module_key = leave`, route submission, inbox visibility, and existing delegation behavior.
5. Approval outcomes and balance mutation: idempotent consumption on approval and reservation release on rejection/cancellation.
6. Anniversary rollover: VL carryover and old-balance-first grace-period consumption, SL payout-interface record, and EL expiry/reset.
7. Manual QA and hardening: regression tests, concurrency/idempotency checks, policy examples, browser verification, audit review, and documentation synchronization.

The exact implementation may split a slice into smaller commits, but no slice may bypass the dependency order or duplicate Approval Engine behavior.

## 7. Explicitly out of the first implementation

- Payroll processing of Sick Leave payout records.
- Notifications.
- Roles and Permissions hardening.
- Post-approval HR reversal/cancellation.
- Hourly Leave or arbitrary fractional units.
- Complex attachment/document storage.
- Advanced policy-engine authoring.

## 8. Business-policy status

No unresolved business-policy question blocks implementation. The approved policies above are the baseline. Implementation may still require technical decisions such as exact calendar configuration storage and the repository-specific event/service hook for Approval Engine outcomes; those are implementation details and must not change the approved business rules without user approval.
