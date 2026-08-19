# BOIE HRIS AI Handoff

## 2026-08-19 Leave Slice 2 Update

- Implemented Leave Slice 2 — Working Calendar + Leave Day Computation.
- Added configurable Holiday master data with soft-delete archive/restore, date uniqueness, search, pagination, and CRUD validation.
- Added the centralized `WorkingCalendarService` using explicit `Asia/Manila` business-date normalization, weekend rules, active-holiday lookup, inclusive ranges, and full/half-day unit computation.
- Leave filing, Leave request/day persistence, reservations, Approval Engine integration, outcome mutation, rollover, and payroll payout remain unimplemented.
- Current verification: 202 tests passed, 789 assertions.

## 2026-08-14 Leave Slice 1 Update

- Implemented Leave Slice 1 — Leave Types + Anniversary Entitlement Foundation.
- Added configurable Leave Type CRUD, soft-delete archive/restore, search, pagination, and idempotent VL/SL/EL seeding.
- Added regularization-anniversary entitlement cycles and per-type granted-day snapshots with locking and database uniqueness guards.
- Current verification: 202 tests passed, 789 assertions; all migrations applied, 175 routes registered, view cache, and `git diff --check` passed.
- Not implemented: working calendar, Leave filing, overlap validation, reservation lifecycle, Approval Engine Leave integration, approval outcome balance mutation, anniversary rollover execution, SL payroll payout processing, Notifications, Roles & Permissions, and Dashboard changes.

## Project
- Local path: `C:\laragon\www\boie-hris`
- Branch: `main`
- Framework: Laravel
- Database: MySQL
- Local environment: Laragon
- Frontend: AdminLTE / Bootstrap

## Important Rules
- Do not run `php artisan migrate:fresh`.
- Do not delete existing data.
- Do not commit or push without user approval.
- Preserve existing uncommitted work.
- Use Form Requests for validation.
- Avoid database queries inside Blade views.
- Use soft deletes for archive behavior.
- Run the full test suite before every commit.
- Keep controllers thin and follow the existing project architecture.

## Completed Modules

### Employee Foundation
- Employee create
- Employee edit
- Employee profile / Employee 201
- Employee archive
- Success notifications
- Master-data seeding
- Nationality normalization
- Government ID masking
- Null-safe profile rendering
- Automated tests
- Manual testing passed

### Base Master Data
- List
- Search
- Pagination
- Create
- View
- Edit
- Archive
- Restore
- Dependency protection
- Flash messages
- Automated tests
- Manual testing pending

### Department Master Data
- List
- Search
- Pagination
- Create
- View
- Edit
- Archive using soft delete
- Archived listing
- Restore
- Optional Unit assignment
- Active-employee dependency protection
- Flash messages
- Automated tests
- Manual testing pending

### Unit Master Data
- List
- Search
- Pagination
- Create
- View
- Edit
- Archive
- Restore
- Dependency protection
- Flash messages
- Automated tests
- Manual testing pending: browser unavailable in verification environment

### Section Master Data
- List
- Search
- Pagination
- Create
- View
- Edit
- Archive using soft delete
- Archived listing
- Restore
- Independent of Department in business logic
- Active-employee dependency protection
- Flash messages
- Automated tests
- Manual testing pending: browser unavailable in verification environment

### Company Master Data
- List
- Search
- Pagination
- Create
- View
- Edit
- Archive
- Restore
- Dependency protection
- Flash messages
- Automated tests
- Manual testing passed

### Employment Status
- List
- Search
- Pagination
- Create
- View
- Edit
- Archive
- Restore
- Dependency protection
- Flash messages
- Automated tests
- Manual testing passed

### Employee Class
- List
- Search
- Pagination
- Create
- View
- Edit
- Archive
- Restore
- Dependency protection
- Flash messages
- Automated tests
- Manual testing passed

### Position Master Data
- List
- Search
- Pagination
- Create
- View
- Edit
- Archive using soft delete
- Archived listing
- Restore
- Independent of Section in business logic
- Unique position code validation
- Active-employee dependency protection
- Flash messages
- Automated tests
- Manual testing pending: browser unavailable in verification environment

### Blue & Green UI Refresh
- Global AdminLTE layout refreshed with blue navigation, green active states, consistent cards, buttons, forms, tables, alerts, badges, and pagination styling
- Completed master-data modules refreshed: Company, Base, Unit, Department, Section, Position, Employment Status, and Employee Class
- Reusable master-data partials updated for search, validation, status badges, table presentation, and form actions
- Controllers, services, Form Requests, routes, database schema, authorization, and archive/restore behavior unchanged
- Automated tests and Blade view cache verification passed

## Employee Class Values
- Rank and File
- Supervisory
- Confidential
- Managerial 1
- Managerial 2
- Executive

## Current Test Status
- Full suite: 202 tests passed
- Assertions: 789
- Filing-phase manual QA passed; broader browser smoke testing remains unavailable in the verification environment

## Current Master Data Status

Completed:
- Company
- Employment Status
- Employee Class
- Base
- Unit
- Department
- Section
- Position

Completed:
- Approval Workflow Foundation pivot implementation: Employee-to-User mapping, eligible approvers, reusable workflow templates, scoped delegation, and append-only audit logging
- Approval Pivot Foundation manual QA and final regression verification: 143 tests passed, 560 assertions
- Approval Engine Runtime / Request Snapshot Foundation backend implementation: request snapshots, immutable ordered steps, append-only request actions, sequential approve/reject/cancel services, runtime delegation resolution, and idempotency handling
- Approval Engine Runtime regression verification: 163 tests passed, 612 assertions
- Employee Request Filing / Approver Selection Foundation: authenticated requester resolution, reusable eligible-approver picker, ordered route preview, HR-final append, real runtime submission, immutable snapshot confirmation, and missing-workflow handling
- Employee Request Filing / Approver Selection Foundation final regression: 170 tests passed, 645 assertions
- Approver Inbox / Approve-Reject-Cancel UI: current-step inbox, delegation-aware detail/actions, requester cancellation, action history, and server-side access control; phase regression was 178 tests and 700 assertions
- Leave Slice 2: database-configured Holiday master data and centralized Asia/Manila Working Calendar + Leave Day Computation

Pending:
- Employee Documents workflow
- Emergency Contact workflow
- Roles and Permissions
- Attendance
- Leave
- Payroll

## Next Planned Work
The Approval Pivot Foundation, Approval Engine Runtime / Request Snapshot Foundation, Employee Request Filing / Approver Selection Foundation, Approver Inbox / Approve-Reject-Cancel UI, Leave Slice 1, and Leave Slice 2 are implemented and regression-verified. The next phase is Leave filing and balance reservation based on the approved architecture in `docs/LEAVE-MODULE-ARCHITECTURE.md`.

Then:
1. Leave filing and balance reservation
2. Employee Documents workflow
3. Emergency Contact workflow
4. Roles and Permissions
5. Attendance
6. Leave
7. Payroll

The backend Runtime Foundation implements employee-selected ordered approver snapshots, request-time route persistence, immutable sequential runtime execution, runtime delegate resolution, approve/reject/cancel services, append-only action history, and idempotency. The filing phase provides the generic `/approval-demo` harness, and the completed inbox phase provides `/approval-inbox` and request action UI. Leave-specific implementation follows the approved design in `docs/LEAVE-MODULE-ARCHITECTURE.md`; Employee Documents, Roles and Permissions, Notifications, and Dashboard integration remain future work.

Manual QA passed for sequential approval: Michelle -> Marielle -> Ronna HR -> Approved. Leave Slice 1 manual QA also passed for VL/SL/EL seeding, Leave Type edit/archive/restore, Employee #1 anniversary cycle 2026-04-04 through 2027-04-03, 15/15/10 grants, zero initial reserved/consumed values, and repeated idempotent lookups. Leave filing, calendar computation, reservations, and approval integration remain unimplemented.

## Approved Organization Decisions

- Implementation order: Base, Unit, Department, Section, Position.
- Base and Unit support monitoring, reporting, and filtering only.
- Employee Create/Edit uses independent organization dropdowns; no cascading dropdowns or strict Base → Unit → Department hierarchy is required.
- Departments may be selected without a Unit and cannot be archived while assigned to active employees.
- Section and Position remain separate assignment fields.
- Position codes must be unique; position names should be descriptive.
- Cascading or filtered organization dropdowns are a future enhancement, outside the current scope.

## Approved Approval Architecture Pivot

- Employees choose ordered approvers at request submission time; employees do not receive fixed workflow assignments.
- Approval is strictly sequential. Employees may add or remove approver rows before submission, cannot select themselves, and cannot select duplicate or ineligible approvers.
- Eligible approvers are active, non-archived employees with `can_approve_requests = true`; the picker should prioritize supervisor, department head, and recently used eligible approvers without routing automatically through those fields.
- HR final approval is automatically appended by the system and cannot be removed by the employee.
- `immediate_supervisor_id` and `department_head_id` remain organizational/informational fields and may only provide suggestions.
- Approval Workflows remain reusable module/template rules with `module_key`, approver limits, HR-final configuration, and active/inactive lifecycle; they do not store fixed employee approver chains.
- Submitted request routes are snapshotted into immutable approval steps. Workflow/template changes and employee or organization changes must not rewrite submitted routes.
- Delegation is scoped for v1 to All Approvals or Specific Department, with future module-specific scope reserved.
- Audit history remains append-only, and Employee-to-User mapping remains required for authenticated approval actions.

The previous fixed `workflow_assignments` and fixed employee approver-chain implementation is uncommitted and was superseded before commit. Applied legacy rows are preserved under legacy table names. See `docs/APPROVAL-ENGINE-DESIGN.md` for the Foundation/Engine boundary and runtime plan.

## Standard Master Data Behavior
Each master-data module should follow the same pattern:

- Index
- Search
- Pagination
- Create
- Show
- Edit
- Archive using soft delete
- Archived listing
- Restore
- Confirmation before archive
- Flash messages
- Dependency protection
- Feature tests
- Manual verification

## Verification Commands

```bash
cd C:\laragon\www\boie-hris
php artisan optimize:clear
php artisan db:seed
php artisan test
php artisan route:list
php artisan view:cache
git diff --check
git status
