# BOIE HRIS - Current Work

## 2026-08-19 Leave Slice 3 Update

- Leave Slice 3 is complete: Leave request/day domain foundation, configurable VL/SL/EL filing timing, validation, overlap rules, draft snapshot persistence, available-balance calculation, VL old-before-new allocation, and reservation/ledger foundation.
- Draft/preview UI is available, but it cannot create `approval_requests` or transition a user-visible request to Pending.
- Verification baseline: 217 tests passed, 846 assertions.
- Next: Slice 4 atomic Leave submission with generic Approval Engine integration.

## 2026-08-19 Leave Slice 2 Update (historical)

- Leave Slice 2 is complete: Holiday master data and centralized Working Calendar + Leave Day Computation.
- Business-calendar dates use explicit `Asia/Manila`; application-wide UTC configuration was unchanged.
- No holiday dates were seeded because no authoritative holiday source was provided.
- Verification baseline: 202 tests passed, 789 assertions.
- Next: Leave filing and balance reservation; Leave request/day persistence remains unimplemented.

## 2026-08-14 Leave Slice 1 Update (historical)

- Leave Slice 1 is complete: Leave Types, idempotent VL/SL/EL seeding, regularization-anniversary cycles, entitlement snapshots, and CRUD/archive/restore.
- Next slice: Working Calendar and day computation.
- Verification: 202 tests passed, 789 assertions; all migrations applied, 175 routes registered, view cache, and diff check passed.
- Pending: working calendar, Leave filing, overlap validation, reservations, Approval Engine integration, approval outcomes, anniversary rollover, SL payroll payout processing, Notifications, Roles & Permissions, and Dashboard changes.

Version: 1.0

---

# Current Module

Leave Slice 3 final audit; next task is atomic Leave submission with Approval Engine integration

---

# Current Task

Complete Slice 3 audit and documentation synchronization; final Approval submission remains deferred to Slice 4.

---

# Status

Approval Pivot Foundation, Approval Engine Runtime / Request Snapshot Foundation, Employee Request Filing / Approver Selection Foundation, Approver Inbox / Approve-Reject-Cancel UI, and Leave Slices 1–3 implemented; atomic Leave/Approval submission and later Leave slices remain pending.

---

# Completed

- Laravel Foundation
- Authentication
- Organization Master Data
- Employee Foundation
- Company
- Employment Status
- Employee Class
- Base Master Data
- Unit Master Data
- Department Master Data
- Section Master Data
- Position Master Data
- Blue & Green UI Refresh for global layout and completed master-data modules
- Employee CRUD
- Employee Contact
- Employee Address
- Employee Government IDs
- Approval Workflow Foundation: Employee-to-User mapping, eligible approvers, reusable workflow templates, scoped delegation, and append-only audit logging
- Approval Engine Runtime / Request Snapshot Foundation: immutable request and step snapshots, sequential actions, delegation, audit logging, and idempotency
- Employee Request Filing / Approver Selection Foundation: authenticated requester resolution, reusable eligible-approver picker, suggestions, ordered route preview, isolated generic demo harness, and runtime submission integration
- Approver Inbox / Approve-Reject-Cancel UI: current-step inbox, delegation-aware request detail, approve/reject actions, requester cancellation, action history, and server-side access control

---

# Remaining Tasks

- Actual Leave request module
- Notifications
- Roles & Permissions
- Dashboard integration
- Employee Documents workflow
- Emergency Contact workflow
- Attendance
- Leave
- Payroll

Future enhancement only:
- Cascading or filtered organization dropdowns; not part of the current implementation scope.

---

# Current Objective

Approval Pivot Foundation, Approval Engine Runtime / Request Snapshot Foundation, Employee Request Filing / Approver Selection Foundation, Approver Inbox / Approve-Reject-Cancel UI, and Leave Slices 1–3 are complete. The next phase is atomic Leave filing with generic Approval Engine submission from the approved architecture in `docs/LEAVE-MODULE-ARCHITECTURE.md`.

---

# Current Branch

main

---

# Next Task

Implement atomic Leave filing plus generic Approval Engine submission, including the real Pending transition.

Approved current sequence: Leave Slice 1, Leave Slice 2, Leave Slice 3 filing/reservation foundation, Slice 4 atomic Approval Engine submission, Slice 5 approval outcomes and balance mutation, anniversary rollover, then remaining operational modules.

---

# Important Notes

- Follow AI Development Guide.
- Keep Controllers thin.
- Use Form Requests.
- Use Service Layer.
- Use Eloquent Relationships.
- Never modify unrelated modules.
- Update CHANGELOG.md and SPRINT-LOG.md after completion.

---

# Verification Status

- Final automated verification: 217 tests passed, 846 assertions.
- Manual QA: Approval Pivot Foundation flows passed, including eligibility, templates, activation protection, scoped delegation, mapping, unmap, and audit logging.
- Filing-phase manual QA passed for requester mapping, workflow availability, eligible search, suggestions, ordering, preview, HR-final append, submission, immutable snapshots, and initial request state.
- Approver Inbox manual QA passed for sequential approval: Michelle -> Marielle -> Ronna HR -> Approved; the tested request was approval_demo, not Leave.
- Runtime verification: 163 tests passed, 612 assertions.
- Runtime backend implemented: request snapshots, sequential steps, runtime delegation, approve/reject/cancel actions, append-only action history, audit events, and idempotency handling.
- Employee Request Filing / Approver Selection Foundation is implemented and regression-verified, with the generic filing/route preview harness available at `/approval-demo`.
- Leave Slices 1–3 are implemented. Atomic Leave/Approval submission, approval outcomes, rollover execution, Employee Documents, Emergency Contacts, Roles & Permissions, Notifications, Dashboard, Attendance, and Payroll remain future work.

---

# Last Updated

2026-08-19
