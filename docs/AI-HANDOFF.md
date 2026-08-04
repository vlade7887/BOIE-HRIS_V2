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
- Full suite: 72 tests passed
- Assertions: 263

## Current Master Data Status

Completed:
- Company
- Employment Status
- Employee Class

Pending:
- Base
- Unit
- Department
- Section
- Position
- Cascading organization dropdowns in Employee Create/Edit
- Employee Documents workflow
- Emergency Contact workflow
- Roles and Permissions
- Attendance
- Leave
- Payroll

## Next Planned Work
Implement Base using the completed Company module as the exact reference pattern.

Then:
1. Unit
2. Department
3. Section
4. Position
5. Cascading organization dropdowns in Employee Create/Edit
6. Employee Documents workflow
7. Emergency Contact workflow
8. Roles and Permissions
9. Attendance
10. Leave
11. Payroll

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
