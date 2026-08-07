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
| HR operational modules | Attendance, leave, payroll, and related workflows | Planned |

## Next Priorities

1. Implement Approval Workflow.
2. Complete Employee Documents and Emergency Contact workflows.
3. Define and implement Roles and Permissions before broader HR operations.
4. Implement Attendance, Leave, and Payroll.

## Completed Since Last Roadmap Update

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

Base → Unit → Department → Section → Position → Organization Cleanup → Reusable Master Data UI Refactor → Blue and Green UI Refresh → Approval Workflow → Employee Documents → Emergency Contacts → Roles and Permissions → Attendance → Leave → Payroll

## Approved Organization and Approval Decisions

- Base and Unit are for monitoring, reporting, and filtering. Employee Create/Edit keeps independent dropdowns; cascading or filtered dropdowns are future scope.
- Department may be selected without a Unit and cannot be archived while assigned to active employees.
- Section and Position remain separate assignment fields. Position codes are unique and names are descriptive.
- Approval Workflow follows Position and precedes Employee Documents and Leave.
- Approval Workflow supports multiple ordered employee approvers with approval levels/order, including two or more signatories, and is reusable by Leave and future approval-based modules. HR remains the final processing stage where applicable.

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
