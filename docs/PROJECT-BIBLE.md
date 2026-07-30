# BOIE HRIS - Project Bible

Version: 1.0
Status: Active
Owner: BOIE Incorporated
System: Human Resource Information System (HRIS)

---

# 1. Project Vision

The BOIE HRIS is an internal Human Resource Information System designed to centralize employee information, attendance, leave, payroll preparation, and HR reporting.

Primary Goals

- Eliminate manual HR processes.
- Maintain a single source of employee information.
- Automate attendance and payroll preparation.
- Reduce encoding errors.
- Improve HR reporting.
- Build a scalable and maintainable Laravel application.

---

# 2. Project Scope

Included Modules

- Employee Management
- Organization Management
- Attendance
- Leave Management
- Payroll Preparation
- Reports
- System Administration

Future Modules

- Recruitment
- Performance Evaluation
- Training
- Asset Management
- Employee Self-Service
- Mobile Access

---

# 3. Technology Stack

Framework
Laravel 13

Language
PHP 8.3

Frontend
Bootstrap 5
AdminLTE

Database
MySQL

Architecture
MVC + Service Layer

---

# 4. Architecture Principles

Controllers must remain thin.

Business logic belongs inside Services.

Validation belongs inside Form Requests.

Database operations must use Eloquent.

Related updates must use DB::transaction().

Never place business logic inside Blade views.

Never hardcode lookup values.

---

# 5. Organization Hierarchy

Company

↓

Base

↓

Unit

↓

Department

↓

Section

↓

Position

↓

Employee

---

# 6. Employee Rules

Every employee must have:

- Employee Number
- Company
- Base
- Department
- Position
- Employment Status
- Employee Class

Employee Number must be unique.

Employee Number is manually entered.

Database ID is the primary key.

Employee Number is the business identifier.

---

# 7. Employment Status

Statuses are maintained in a master table.

Example

- Regular
- Probationary
- Contractual
- Project-Based

---

# 8. Employee Class

Employee classes are maintained in a master table.

Example

- Rank and File
- Supervisor
- Manager
- Confidential

---

# 9. Attendance

Attendance will be imported from biometric devices.

No manual attendance encoding unless authorized.

Attendance will become the source for payroll computation.

---

# 10. Leave

Leave requests require approval workflow.

Leave balances are maintained by the system.

Supported leave types include:

- Vacation Leave
- Sick Leave
- Birthday Leave

Future leave types may be added.

---

# 11. Payroll

Payroll uses attendance as the primary source.

Payroll rules must remain configurable.

Payroll computation must never use hardcoded values.

---

# 12. Security

Use authentication.

Use authorization.

Protect employee personal information.

Protect government IDs.

Never expose sensitive employee data.

---

# 13. Coding Principles

Follow Laravel standards.

Use Resource Controllers.

Use Form Requests.

Use Service Layer.

Use Transactions.

Use Eloquent Relationships.

Never duplicate logic.

---

# 14. Documentation Policy

Whenever architecture changes:

Update:

- PROJECT-BIBLE.md
- PROJECT-ARCHITECTURE.md
- CHANGELOG.md
- SPRINT-LOG.md

---

# 15. AI Development Policy

Every AI assistant must:

Read all documentation first.

Follow existing architecture.

Preserve coding standards.

Never redesign working modules without user approval.

Never modify unrelated modules.

Always provide a completion summary.

---

# 16. Current Module Status

✅ Employee Module

🔄 Master Data Import

⬜ Employee Import

⬜ Administration

⬜ Attendance

⬜ Leave

⬜ Payroll

⬜ Reports

---

# 17. Business Rules

This document will be continuously updated as BOIE HRIS grows.

Future business rules will include:

- Attendance Policies
- Leave Policies
- Payroll Computation Rules
- Overtime Rules
- Holiday Rules
- Late and Undertime Rules
- Approval Workflows
- Government Compliance
- Company Policies

These rules become the official business reference of BOIE HRIS.

# 18. Long-Term Goal

The BOIE HRIS should become the company's single source of truth for employee information and HR operations.

The system should remain modular, maintainable, scalable, and AI-friendly for future development.

---

End of Document

