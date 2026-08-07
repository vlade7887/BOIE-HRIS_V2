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
- Full suite: 127 tests passed
- Assertions: 486
- Manual browser smoke testing pending because no browser was available in the verification environment

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

Pending:
- Approval Workflow
- Employee Documents workflow
- Emergency Contact workflow
- Roles and Permissions
- Attendance
- Leave
- Payroll

## Next Planned Work
Blue & Green UI Refresh is complete. Approval Workflow is the next phase.

Then:
1. Approval Workflow
2. Employee Documents workflow
3. Emergency Contact workflow
4. Roles and Permissions
5. Attendance
6. Leave
7. Payroll

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
php artisan route:list
php artisan view:cache
git diff --check
git status
