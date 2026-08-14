# BOIE HRIS Approval Architecture and Migration Plan

Status: Approval Pivot Foundation, Approval Engine Runtime / Request Snapshot Foundation, Employee Request Filing / Approver Selection Foundation, and Approver Inbox / Approve-Reject-Cancel UI implemented; next phase is Actual Leave Module / Approval Engine integration design

Date: 2026-08-07

This document records the approved replacement for the uncommitted fixed workflow-assignment approach. The Foundation configuration, Runtime request snapshot backend, generic employee filing/approver-selection harness, and delegation-aware Approver Inbox UI are implemented and regression-verified. Leave integration, Notifications, Dashboard integration, Employee Documents, Emergency Contacts, and Roles and Permissions remain future work.

Manual QA passed for sequential approval: Michelle -> Marielle -> Ronna HR -> Approved. The tested request was an `approval_demo` request, not an actual Leave request. Leave-specific request data and rules remain unimplemented.

## 1. Architecture Boundary

### A. Approval Workflow Foundation

The Foundation owns reusable configuration and identity data:

- Employee-to-User one-to-one mapping for authenticated approval actions.
- Employee-level `can_approve_requests` eligibility capability.
- Reusable Approval Workflow module/template rules.
- Scoped temporary delegation.
- Append-only approval audit logging.

The Foundation does not assign a fixed workflow to an employee and does not store a fixed employee approver chain.

### B. Approval Engine Runtime

At request submission time, the requester selects ordered eligible approvers. The system rejects self-selection and duplicates, validates the configured minimum and maximum, appends the configured HR final approver, and snapshots the resulting route into immutable request steps.

Approval is strictly sequential. Only the active step can be acted upon. A delegation is evaluated when the step becomes active or is acted upon; the canonical approver remains the selected employee, while the action records the delegate who acted for that canonical approver.

### C. Future Leave Integration

Leave will later create request data consumed by the reusable Approval Engine. Leave balances, Leave forms, Leave-specific rules, Notifications, and Dashboard integration are separate future work.

## 2. Current Uncommitted Inventory

The current Foundation implementation is uncommitted. The fixed employee assignment and fixed workflow-step portions are superseded before commit and must not be treated as production architecture.

### Retain

- `app/Models/Employee.php` and `app/Models/User.php` Employee-to-User relationships.
- `2026_08_07_000020_add_user_id_to_employees_table.php`, subject to retaining explicit mapping behavior.
- `EmployeeUserMappingController`, `EmployeeUserMappingService`, `UpdateEmployeeUserMappingRequest`, mapping views, and mapping routes.
- `ApprovalAuditLog`, `ApprovalAuditService`, `ApprovalAuditLogController`, audit views, and `2026_08_07_000025_create_approval_audit_logs_table.php`.
- `ApprovalDelegation` as a concept, after scoped-delegation refactoring.
- `ApprovalWorkflow` as a concept, after template/rule refactoring.
- Existing Foundation documentation only where rewritten by this pivot.

### Remove from the approved architecture

- `workflow_assignments` and its fixed Employee-to-Workflow assignment model.
- `workflow_steps` as a fixed employee approver-chain table.
- `WorkflowAssignment` and `WorkflowStep` models, services, controllers, requests, views, routes, and tests.
- Applied migrations `2026_08_07_000022_create_workflow_assignments_table.php` and `2026_08_07_000023_create_workflow_steps_table.php` remain untouched; forward migration `2026_08_07_000029_rename_retired_workflow_tables.php` preserves their rows under legacy table names.

If either migration has already been applied to a database, do not drop the table or delete records blindly. First inspect migration status and data ownership. Any forward cleanup migration must preserve records or archive them under an explicitly approved data-retention decision.

### Refactor

- `ApprovalWorkflow` migration, model, requests, controller, service, views, and routes: retain code/version identity and lifecycle status, but store reusable module/template rules rather than fixed approvers.
- `ApprovalDelegation` migration, model, requests, controller, service, views, and routes: add v1 scope and conflict validation.
- `Employee`: add the approver capability field without adding `user_id` back to mass assignment.
- Foundation tests: remove fixed-assignment/step-chain assertions and replace them with pivot-aligned Foundation tests.

## 3. Proposed Foundation Schema

### `employees`

Add:

- `can_approve_requests` boolean, non-null, default `false`.

Keep `user_id` nullable and unique. Mapping remains an explicit service operation; `user_id` must remain excluded from `Employee::$fillable`.

Eligibility for the picker requires an active, non-archived employee with `can_approve_requests = true`. Authenticated approval action additionally requires Employee-to-User mapping.

### `approval_workflows`

Keep:

- `code`
- `version`
- `name`
- `description`
- `status`

Valid statuses remain `draft`, `active`, `inactive`, and `archived`. Identity uniqueness remains `code + version`.

Add:

- `module_key` varchar(50), nullable only for preserved legacy compatibility; new and updated templates require one of the controlled module keys.
- Controlled v1 module keys: `leave`, `overtime`, `official_business`, and `undertime`.
- `min_approvers` unsigned small integer, required.
- `max_approvers` unsigned small integer, required.
- `hr_final_required` boolean, non-null, default `true`.
- `hr_final_approver_employee_id` nullable foreign key to `employees` for the configured HR final approver.

When `hr_final_required` is true, the configured HR approver must be active, non-archived, and eligible. The employee cannot remove this final step at submission.

### `approval_delegations`

Keep:

- `acting_for_employee_id`
- `delegate_employee_id`
- `effective_from`
- `effective_until`
- `reason`
- `status`
- `revoked_at`
- `revoked_by_user_id`

Add:

- `scope_type` varchar(20), required, values `all` or `department`, default `all`.
- `department_id` nullable foreign key to `departments`.

Scope integrity rule:

- `scope_type = all` requires `department_id = null`.
- `scope_type = department` requires a non-null department.

V1 delegation scope is All Approvals or Specific Department. Module-specific delegation remains a future extension; the schema keeps the scope model extensible.

### `approval_audit_logs`

Retain the append-only structure using:

- `event_type`
- `actor_user_id`
- `actor_employee_id`
- `auditable_type`
- `auditable_id`
- `correlation_id`
- `metadata`
- `ip_address`
- `user_agent`
- `occurred_at`
- `created_at`

There is no update or delete workflow for audit records.

## 4. Implemented Runtime Snapshot Schema

These tables are implemented by the Approval Engine Runtime / Request Snapshot Foundation. They remain generic and do not depend directly on Leave tables.

### `approval_requests`

- `id`
- `requester_employee_id` foreign key
- `requestable_type` varchar(150)
- `requestable_id` unsigned big integer
- `module_key` varchar(50)
- `approval_workflow_id` nullable foreign key for provenance only
- `workflow_code` varchar(50) snapshot
- `workflow_version` unsigned small integer snapshot
- `status` varchar(20)
- `submitted_at` nullable timestamp
- `completed_at` nullable timestamp
- `created_at`
- `updated_at`

The requestable record and workflow provenance do not control the already-snapshotted route.

### `approval_request_steps`

- `id`
- `approval_request_id` foreign key with cascade on request removal
- `step_order` unsigned small integer
- `approver_employee_id` foreign key containing the canonical selected approver
- `is_hr_step` boolean marker
- `status` varchar(20)
- `activated_at` nullable timestamp
- `acted_at` nullable timestamp
- `acted_by_employee_id` nullable foreign key containing the employee who actually acted
- `delegation_id` nullable foreign key for the delegation used, if any
- `decision` nullable varchar(20)
- `remarks` nullable text
- `created_at`
- `updated_at`

Unique identity is `approval_request_id + step_order`. Route columns (`step_order`, canonical approver, and HR marker) are immutable after submission. Only runtime state and action fields may change.

## 5. HR Final Approval Configuration

For v1, `approval_workflows.hr_final_required` and `approval_workflows.hr_final_approver_employee_id` represent the configured automatic HR final step. At submission, the Engine appends this employee after the requester-selected rows and writes it to the immutable request-step snapshot with `is_hr_step = true`.

The requester never submits or removes the HR row. If a future requirement needs an HR approver pool, that is a separate approved design decision; it is not introduced now.

## 6. Required Service-Layer Rules

### Workflow/template configuration

- Validate `code + version` uniqueness.
- Validate allowed lifecycle statuses.
- Validate `min_approvers >= 1` and `max_approvers >= min_approvers`.
- Validate configured HR final approver eligibility when HR final approval is required.
- Treat active configuration as immutable; create a new version for changes.

### Request-time route selection

- Employee cannot select themselves.
- Duplicate approvers are rejected.
- Every selected approver must be active, non-archived, and `can_approve_requests = true`.
- Suggested supervisor and department-head values are not automatic routing.
- Selected count must be within the active workflow's configured limits.
- HR final approval is appended exactly once and cannot be removed.
- Snapshot creation and request submission occur in one transaction.

### Delegation

- Reject self-delegation.
- Require active, non-archived acting-for and delegate employees.
- Reject overlapping active delegations for the same acting-for employee when scopes conflict: All conflicts with every department scope; department conflicts with the same department and All.
- Reject direct A -> B and B -> A loops over overlapping effective periods and conflicting scopes.
- Revoke only through the dedicated action, setting `status`, `revoked_at`, and `revoked_by_user_id` together.
- Normal create/edit forms cannot manually set `expired` or `revoked`.
- Preserve the canonical approver when a delegate acts.

## 7. Required Tests

Foundation tests should cover:

- Employee capability default and eligibility filtering.
- Explicit one-to-one Employee-to-User mapping.
- Workflow code/version uniqueness, lifecycle rules, and active immutability.
- Workflow approver limits and HR-final configuration.
- Self-selection and duplicate approver rejection.
- Inactive, archived, and non-capable approver rejection.
- Scoped delegation validity, conflicts, overlap, direct loops, and revoke metadata.
- Append-only audit fields, actor mapping, correlation ID, request metadata, and occurrence time.
- Absence of fixed assignment and fixed workflow-step routes.

Runtime tests cover snapshot immutability, sequential activation, HR appending, delegation evaluation, canonical-versus-acting employee records, request cancellation/rejection, action idempotency, and resilience to later employee or organization changes.

## 8. Safe Implementation Order

1. Preserve Employee-to-User mapping and audit work; do not alter production/master-data records.
2. Preserve applied assignment/step rows through the safe forward legacy-table rename.
3. Implement and verify reusable workflow/template rules, employee capability, and scoped delegation fields.
4. Implement and verify Foundation services, requests, controllers, views, routes, and tests.
5. Runtime snapshot tables and Engine services are implemented and verified; keep the Engine generic and separate from Leave.
6. Add Leave integration only after the Engine is verified.
7. Run migrations safely against the intended environment, never with `migrate:fresh`, then run the full verification suite.

## 9. Risks and Open Questions

- V1 uses exactly one configured HR final approver employee when HR final approval is required.
- Controlled v1 module keys are `leave`, `overtime`, `official_business`, and `undertime`; workflow identity is unique by `code + version`.
- New templates default to `min_approvers = 1` and `max_approvers = 5`; maximum allowed is 20.
- Applied assignment/step migrations were confirmed and handled through a safe forward rename migration.
- Authentication remains the temporary access boundary until Roles and Permissions is implemented.
