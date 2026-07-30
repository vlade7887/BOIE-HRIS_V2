# BOIE HRIS - Development Standards

Version: 1.0

Status: Active

---

# 1. Purpose

This document defines the coding and development standards for BOIE HRIS.

Every developer and AI coding assistant must follow these standards.

---

# 2. General Principles

- Follow Laravel best practices.
- Keep the code clean, readable, and maintainable.
- Prefer readability over clever code.
- Make the smallest safe change that satisfies the requirement.
- Do not modify unrelated files.

---

# 3. Controller Standards

Controllers must remain thin.

Controllers are responsible only for:

- Receiving HTTP requests
- Returning views
- Redirecting users
- Calling Services

Controllers must never contain business logic.

---

# 4. Form Request Standards

Use Form Requests for all validation.

Examples

- StoreEmployeeRequest
- UpdateEmployeeRequest
- StoreCompanyRequest
- UpdateCompanyRequest

Never validate directly inside Controllers.

---

# 5. Service Layer Standards

Business logic belongs inside Services.

Examples

- EmployeeService
- MasterDataImportService
- AttendanceService
- LeaveService
- PayrollService

Services should be reusable and testable.

---

# 6. Model Standards

Models should contain:

- Relationships
- Attribute casts
- Accessors
- Mutators
- Query scopes

Avoid placing complex business logic inside Models.

---

# 7. Blade Standards

Blade templates are for presentation only.

Avoid database queries inside Blade.

Avoid business logic inside Blade.

Use Blade components where appropriate.

---

# 8. Route Standards

Use resource routes whenever possible.

Example

Route::resource('employees', EmployeeController::class);

Use route model binding.

---

# 9. Naming Conventions

Controllers

EmployeeController

Services

EmployeeService

Requests

StoreEmployeeRequest

Models

Employee

Migrations

create_employees_table

Views

employees/index.blade.php

employees/create.blade.php

employees/edit.blade.php

---

# 10. Error Handling

Handle exceptions gracefully.

Return user-friendly messages.

Log unexpected errors.

Do not expose stack traces in production.

---

# 11. Transactions

Use DB::transaction() whenever multiple related tables are modified.

Example

Employee

↓

Employee Contact

↓

Employee Address

↓

Government IDs

---

# 12. Documentation

When a feature changes:

Update

- CHANGELOG.md
- SPRINT-LOG.md

When architecture changes:

Update

- PROJECT-BIBLE.md
- PROJECT-ARCHITECTURE.md

---

# 13. Testing

Every completed feature should include:

- Validation testing
- Feature testing (when applicable)
- Database verification

Run:

git diff --check

php artisan test

php artisan view:cache

(if applicable)

---

# 14. Security

Never commit:

- Passwords
- API Keys
- Sensitive employee information

Always validate user input.

Always use authorization where required.

---

# 15. Code Quality

Prefer:

- Dependency Injection
- Eloquent Relationships
- Service Layer
- Form Requests

Avoid:

- Hardcoded values
- Duplicate logic
- Large Controllers
- Large Methods

---

# 16. Refactoring Rules

Refactor code only when:

- It improves readability.
- It reduces duplicated logic.
- It preserves existing behavior.
- It does not introduce breaking changes.

Avoid unnecessary refactoring during feature implementation.

Never refactor unrelated modules unless explicitly requested by the user.


# 17. Pull Request / Completion Checklist

Before considering a task complete:

✓ Requirements implemented

✓ Tests passed

✓ No unrelated files modified

✓ Documentation updated

✓ Existing architecture preserved

✓ No hardcoded values

✓ Transactions used where required

✓ Summary provided

---

# 18. AI Development Workflow

For every coding task:

1. Read documentation first.
2. Understand the requirement.
3. Plan the implementation.
4. Implement the smallest safe change.
5. Run verification.
6. Update documentation if needed.
7. Provide a completion summary.

---

# 19. Coding Philosophy

The BOIE HRIS codebase should be:

- Simple
- Consistent
- Modular
- Testable
- Maintainable
- Scalable

Every new feature must improve the project without increasing unnecessary complexity.

---

End of Document