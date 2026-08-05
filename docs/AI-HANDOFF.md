# BOIE HRIS AI Handoff

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

## Employee Class Values
- Rank and File
- Supervisory
- Confidential
- Managerial 1
- Managerial 2
- Executive

## Current Test Status
- Full suite: 94 tests passed
- Assertions: 341

## Current Master Data Status

Completed:
- Company
- Employment Status
- Employee Class
- Base
- Unit

Pending:
- Department
- Section
- Position
- Approval Workflow
- Employee Documents workflow
- Emergency Contact workflow
- Roles and Permissions
- Attendance
- Leave
- Payroll

## Next Planned Work
Implement Department using the completed Company module as the exact reference pattern.

Then:
1. Section
2. Position
3. Approval Workflow
4. Employee Documents workflow
5. Emergency Contact workflow
6. Roles and Permissions
7. Attendance
8. Leave
9. Payroll

## Approved Organization Decisions

- Implementation order: Base, Unit, Department, Section, Position.
- Base and Unit support monitoring, reporting, and filtering only.
- Employee Create/Edit uses independent organization dropdowns; no cascading dropdowns or strict Base → Unit → Department hierarchy is required.
- Departments may be selected without a Unit and cannot be archived while assigned to active employees.
- Section and Position remain separate assignment fields.
- Position codes must be unique; position names should be descriptive.
- Cascading or filtered organization dropdowns are a future enhancement, outside the current scope.

## Approved Approval Workflow

- Implement after Position and before Employee Documents and Leave.
- Support multiple ordered approvers linked to employees, with approval levels/order for two, three, four, or more signatories.
- Build it for reuse by Leave and future approval-based modules.
- HR remains the final processing stage where applicable.

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
git status
