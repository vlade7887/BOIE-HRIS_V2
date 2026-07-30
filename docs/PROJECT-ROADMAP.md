# BOIE HRIS Project Roadmap

## Product Direction

BOIE HRIS is being established around reliable organization and employee master data. Future operational modules should reuse this foundation rather than create parallel employee or organizational records.

## Delivery Status

| Stage | Scope | Status |
| --- | --- | --- |
| Foundation | Laravel application, Breeze authentication, database-backed sessions and queues | Complete |
| Organization master data | Company, base, unit, department, section, position, employment status, employee class | Complete |
| Employee master foundation | Employee profile and organization assignment | Complete |
| Employee supporting data | Contact, address, government ID, emergency contact, document data structures and CRUD work | In progress |
| HR operational modules | Attendance, leave, payroll, and related workflows | Planned |

## Next Priorities

1. Complete and verify the employee supporting-data workflows, including document storage validation and access handling.
2. Add feature tests for implemented organization and employee CRUD workflows, validation, archival, and protected routes.
3. Define authorization roles and permissions before exposing broader HR operations.
4. Implement employee assignment history so transfers, promotions, and related changes are auditable rather than overwriting historical context.
5. Define document retention, download authorization, and production storage strategy.

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
