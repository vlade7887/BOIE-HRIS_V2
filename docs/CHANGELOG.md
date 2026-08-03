# Changelog

All notable BOIE HRIS project changes are documented in this file.

The project follows a chronological, phase-and-sprint-based changelog while release versioning is not yet established.

## Unreleased

### Employee Create Master Data and Validation Feedback

- Added idempotent development master-data seeding for the Employee create workflow.
- Added visible create-form validation summary, tab indicators, required-field feedback, and the missing closing form tag.
- Added feature coverage for seeded options, validation feedback, employee creation, supporting-record creation, and repeatable seeding.

### Employee Profile View

- Added the read-only Employee 201 profile page with eager-loaded employee, organization, contact, address, emergency-contact, and document metadata.
- Added masked display accessors for government IDs; raw identifiers and document file paths are not rendered on the profile page.
- Added feature coverage for employee-profile display, missing optional relationships, identifier masking, and unknown employee handling.

### Employee Module Completion v1.0

- Completed Employee 201 create and edit persistence for employee, contact, address, and government-ID data.
- Added service-backed transactional updates, populated edit lookup dropdowns, and preserved old input on form validation failures.
- Added optional supporting-data fields for alternate mobile, structured addresses, passport, and driver license.
- Added employee feature coverage for supporting-record creation and updates.

### Sprint 4C.2 — Employee Supporting Records

- Added automatic creation of one contact, address, and government-ID record for every new employee.
- Kept employee and supporting-record writes in the `EmployeeService` transaction.
- Preserved optional supporting data by creating records with nullable defaults.

### Sprint 4C.1 — Employee Service Architecture

- Moved the employee creation transaction from `EmployeeController` to `EmployeeService`.
- Preserved the existing validated data, employee creation behavior, redirect, and flash message.
- Deferred contact, address, and government-ID creation from the employee creation service.

### Planned

- Complete verification and testing of employee supporting-data workflows.
- Add secure employee-document upload and retrieval controls.
- Introduce assignment history and role-based access control.

## 2026-07-29

### Documentation Setup

- Added the project architecture, roadmap, database standards, sprint log, and AI-assistant instructions.
- Refreshed development standards for the current Laravel, Eloquent, Blade, and service-layer approach.
- Established documentation as a required source of guidance for future project changes.

## 2026-07-28 to 2026-07-29

### Sprint 4B Refinement

- Added employee supporting-data structures for contact information, addresses, government IDs, emergency contacts, and documents.
- Added associated models, validation requests, and controller workflows.
- Added `EmployeeService` to support atomic creation of an employee with related contact, address, and government-ID records.
- Continued employee document metadata support for document type, name, file path, remarks, and upload time.

### Sprint 4B Employee Creation

- Implemented the employee creation workflow on the employee-master foundation.
- Added employee personal, employment, organization-assignment, and supervisor/department-head fields.
- Used form-request validation and database transactions for employee persistence.

### Sprint 4A UI Refinement

- Refined the employee UI and application layout while retaining the server-rendered Blade approach.
- Improved the employee list and edit workflow foundation for the Employee 201 experience.

### Sprint 4A Employee 201 UI

- Established the Employee 201 user-interface foundation for viewing and maintaining employee master information.
- Added employee index, create, edit, and related Blade view work.

### Employee Foundation

- Added the central `employees` table and Eloquent model.
- Linked employees to company, base, unit, department, section, position, employment status, and employee class masters.
- Added self-referential optional relationships for immediate supervisor and department head.
- Applied soft deletion and date/boolean casts to employee records.

## 2026-07-28

### Phase 2 Database Design

- Created the organization master-data schema for companies, bases, units, departments, sections, positions, employment statuses, and employee classes.
- Defined the organization hierarchy: base, unit, department, section, and position.
- Added foreign keys, unique business codes, timestamps, soft deletion, and active-state fields for HR master data.
- Added Eloquent models, relationships, resource controllers, and Form Requests for the implemented master-data modules.

### Phase 1 Project Setup

- Initialized BOIE HRIS as a Laravel 13 application on PHP 8.3+.
- Configured MySQL as the application database and database-backed sessions and queues.
- Added Laravel Breeze authentication and the authenticated dashboard route.
- Established the Blade, Vite, Tailwind CSS, and Alpine.js web-application stack.

## Reference History

- `59671a0` — Initial BOIE HRIS foundation (Company, Base, Unit)
- `a9d8f3f` — Complete organization structure and employee master tables
- `a9074a4` — Complete Employee Sprint 1 - Employee master foundation
