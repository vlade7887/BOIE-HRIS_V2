# BOIE HRIS Project Roadmap

## Product Direction

BOIE HRIS is being established around reliable organization and employee master data. Future operational modules should reuse this foundation rather than create parallel employee or organizational records.

## Delivery Status

| Stage | Scope | Status |
| --- | --- | --- |
| Foundation | Laravel application, Breeze authentication, database-backed sessions and queues | Complete |
| Organization master data | Company, base, unit, department, section, position, employment status, employee class | Complete |
| Employee master foundation | Employee profile and organization assignment | Complete |
| Employee supporting data | Contact, address, government ID, emergency contact, document data structures and CRUD work | Planned |
| Approval architecture | Employee-selected sequential routes, reusable workflow rules, scoped delegation, immutable runtime snapshots, and generic filing harness | Foundation and filing UI implemented; Approver Inbox UI and Leave integration planned |
| HR operational modules | Attendance, leave, payroll, and related workflows | Planned |

## Next Priorities

1. Implement Approver Inbox / Approve-Reject-Cancel UI.
2. Complete Employee Documents and Emergency Contact workflows.
3. Define and implement Roles and Permissions before broader HR operations.
4. Implement Attendance, Leave, and Payroll.

## Completed Since Last Roadmap Update

- The previous fixed-assignment Approval Workflow Foundation was superseded before commit; applied legacy assignment/step tables are retained under legacy names with rows preserved.
- The Approval Workflow Foundation pivot implementation now covers Employee-to-User mapping, `can_approve_requests`, reusable workflow/template rules, scoped delegation, and append-only audit logs.
- Approval Pivot Foundation is implemented and manual QA passed; final regression verification is 143 tests passed with 560 assertions.
- Approval Engine Runtime / Request Snapshot Foundation backend is implemented; full regression verification is 163 tests passed with 612 assertions.
- Employee Request Filing / Approver Selection Foundation is implemented and regression-verified: authenticated requester mapping, eligible approver selection, ordered preview, HR-final append, real runtime submission, and read-only confirmation.
- Current final verification: 170 tests passed, 645 assertions; migrations are unchanged and all migrations are applied.
- The approved architecture keeps Employee-to-User mapping, reusable workflow/template rules, scoped delegation, and append-only audit logs; it removes fixed employee workflow assignments and fixed approver chains.
- Approver Inbox / Approve-Reject-Cancel UI, Employee Documents, Emergency Contacts, Roles and Permissions, Notifications, Leave integration, Attendance, and Payroll remain unimplemented.

- Reusable Master Data UI Refactor is complete: common Blade partials now cover validation summaries, standard form fields, search, index tables, and archive/restore actions across the eight completed master-data resources.
- Current verification: 127 tests passed, 486 assertions.
- Next phase: Blue & Green UI Refresh.

- Blue & Green UI Refresh is complete for the global AdminLTE layout and the eight completed master-data modules, using the shared presentation partials and a dedicated application stylesheet.
- Preserved existing functionality and left controllers, services, Form Requests, routes, database schema, authorization, and archive/restore behavior unchanged.
- Current verification: 127 tests passed, 486 assertions; optimize:clear and view:cache passed.
- Next phase: Approval Workflow.

- Section master data is complete: CRUD, search, pagination, soft-delete archive, restore, active-employee dependency protection, and feature tests.
- Position master data is complete: CRUD, search, pagination, soft-delete archive, restore, unique code validation, active-employee dependency protection, and feature tests.
- Organization master-data foundation is complete.
- Organization Cleanup is complete: the eight completed master-data resources now use archive-only routes, with redundant destroy endpoints removed and archive/restore routes retained.
- Current verification: 127 tests passed, 481 assertions.
- Next phase: Reusable Master Data UI Refactor.

## Approved Implementation Order

Base → Unit → Department → Section → Position → Organization Cleanup → Reusable Master Data UI Refactor → Blue and Green UI Refresh → Approval Pivot Foundation → Approval Engine Runtime / Request Snapshot Foundation → Employee Request Filing / Approver Selection Foundation → Approver Inbox / Approve-Reject-Cancel UI → Employee Documents → Emergency Contacts → Roles and Permissions → Attendance → Leave → Payroll

## Approved Organization and Approval Decisions

- Base and Unit are for monitoring, reporting, and filtering. Employee Create/Edit keeps independent dropdowns; cascading or filtered dropdowns are future scope.
- Department may be selected without a Unit and cannot be archived while assigned to active employees.
- Section and Position remain separate assignment fields. Position codes are unique and names are descriptive.
- Approval Workflow follows Position and precedes Employee Documents and Leave.
- Employees choose ordered, unique, eligible approvers at request submission time. Approval is sequential, and HR final approval is automatically appended and cannot be removed.
- Immediate Supervisor and Department Head remain informational fields used only as picker suggestions.
- `can_approve_requests` controls employee-level approver eligibility. Employees must be active and non-archived; authenticated approval actions also require Employee-to-User mapping.
- Approval Workflows are reusable module/template rules with approver limits and HR-final configuration, not fixed employee approver chains.
- Submitted routes are immutable snapshots. Delegation is scoped for v1 to All Approvals or Specific Department; module-specific scope is future work.

## Approval Architecture Boundaries

- Foundation: Employee-to-User mapping, approver eligibility, reusable workflow/template configuration, scoped delegation, and append-only audit logging.
- Approval Engine runtime: request-time approver selection, automatic HR-final appending, immutable request-step snapshots, sequential activation, delegation evaluation, and approval actions.
- Future Leave integration: Leave request data and Leave-specific use of the reusable Approval Engine after the Engine is complete.

## Planned Functional Releases

### Release 1 — Employee Master Completion

- Complete employee supporting records and document handling.
- Improve employee search, filtering, and profile presentation.
- Add data-quality rules for organization assignments and supervisors.
- Establish audit/history requirements for employee changes.

### Release 2 — Workforce Operations

- Attendance and timekeeping integration or capture.
- Leave balances, filing, review, and approval workflows.
- Employee movement and assignment history.
- Role-based access control for HR, managers, and employees.

### Release 3 — Compensation and Insights

- Payroll inputs and payroll integration, subject to approved rules and controls.
- Standard HR and workforce reports.
- Data exports with authorization and privacy safeguards.
- Operational dashboards for HR and management.

### Release 4 — Employee Self-Service

- Employee profile updates with approval workflow.
- Personal document access.
- Leave and attendance self-service functions.
- Notifications and manager approvals.

## Roadmap Principles

- Deliver master-data quality and auditability before dependent operational workflows.
- Treat personal, contact, government-ID, and document information as sensitive data.
- Keep payroll, attendance, and leave rules configurable and documented before implementation.
- Update this roadmap when scope is confirmed; planned items are not commitments until scheduled.
