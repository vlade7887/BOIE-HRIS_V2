# BOIE HRIS

Human Resource Information System for BOIE Incorporated.

---

## Project Information

Framework:
Laravel 13

Language:
PHP 8.3

Database:
MySQL

Architecture:
MVC + Service Layer

Frontend:
Bootstrap 5
AdminLTE

---

## Documentation

Project documentation is located in:

docs/

Before making any code changes, read the documents in the following order:

1. AI Development Guide.md
2. CURRENT-WORK.md
3. PROJECT-BIBLE.md
4. PROJECT-ARCHITECTURE.md
5. DEVELOPMENT-STANDARDS.md
6. DATABASE-STANDARDS.md
7. PROJECT-ROADMAP.md
8. CHANGELOG.md
9. SPRINT-LOG.md
10. Current Module Specification, including `docs/LEAVE-MODULE-ARCHITECTURE.md` for the approved Leave implementation architecture

---

## Development Workflow

Every developer or AI assistant must:

1. Read all required documentation.
2. Review the existing implementation.
3. Report current project status.
4. Wait for user approval before writing code.
5. Follow all project standards.
6. Update documentation after completing work.

---

## Coding Standards

- Keep Controllers thin.
- Business logic belongs in Services.
- Validation belongs in Form Requests.
- Use Eloquent Relationships.
- Use DB::transaction() when updating multiple related tables.
- Never modify unrelated modules.

---

## Current Status

See:

docs/CURRENT-WORK.md

---

## Module Specifications

Module specifications are located in:

docs/

Examples:

- Company-Specification.md
- Employee-Specification.md
- LEAVE-MODULE-ARCHITECTURE.md

Attendance and Payroll specifications remain future documentation items; no `Leave-Specification.md` file is assumed.

---

## AI Instructions

Before writing code:

- Read the required documentation.
- Review the existing implementation.
- Do not assume missing functionality.
- Preserve the current architecture.
- Follow all development standards.
- Report findings before implementation.

---

## License

Internal use only.

BOIE Incorporated.
