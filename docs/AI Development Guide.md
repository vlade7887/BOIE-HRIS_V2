# BOIE HRIS – AI Development Guide

## Purpose

This document defines the permanent development rules for every AI coding assistant working on the BOIE HRIS project.

These instructions apply to Codex, ChatGPT, Claude Code, GitHub Copilot, Cursor AI, Windsurf, and any future AI coding assistant.

Unless the user explicitly overrides a rule, always follow this document.

---

# Required Reading Before Any Task

Before writing code, read these documents in order:

1. PROJECT-BIBLE.md
2. PROJECT-ARCHITECTURE.md
3. DEVELOPMENT-STANDARDS.md
4. DATABASE-STANDARDS.md
5. PROJECT-ROADMAP.md
6. CHANGELOG.md
7. SPRINT-LOG.md

The documentation, database schema, and existing implementation together form the project source of truth.

If documentation conflicts with implementation:

Priority:

1. User instruction
2. Database schema
3. PROJECT-BIBLE.md
4. PROJECT-ARCHITECTURE.md
5. Existing implementation

Never invent business rules.

---

# Project Stack

Framework:
Laravel 13

Language:
PHP 8.3

Frontend:
Bootstrap 5
AdminLTE

Database:
MySQL

Architecture:
MVC + Service Layer

---

# Architecture Rules

Always keep Controllers thin.

Business logic belongs inside Services.

Validation belongs inside Form Requests.

Models represent data only.

Controllers should only:

- receive request
- call service
- return response

Never move business logic into Controllers.

---

# Database Rules

Never hardcode lookup values.

Always use master tables.

Always use foreign keys.

Always preserve referential integrity.

Never edit old migrations.

Always create a new migration.

Never delete production tables.

---

# Transaction Rules

Whenever multiple related tables are modified:

Always use:

DB::transaction()

Example:

Employee

↓

Contact

↓

Address

↓

Government IDs

must always be saved as one transaction.

---

# Coding Standards

Use:

- Dependency Injection
- Route Model Binding
- Eloquent Relationships
- Form Requests
- Service Layer
- Typed Parameters
- Return Types

Avoid:

- duplicated logic
- magic numbers
- hardcoded IDs
- raw SQL unless necessary

---

# Naming Standards

Controllers

EmployeeController

Services

EmployeeService

Requests

StoreEmployeeRequest

UpdateEmployeeRequest

Models

Employee

EmployeeContact

EmployeeAddress

Views

employees/index

employees/create

employees/edit

---

# Module Boundaries

Current Modules

Employee

Administration

Attendance

Leave

Payroll

Reports

Never modify another module unless requested.

---

# UI Rules

Preserve AdminLTE layout.

Preserve Bootstrap structure.

Do not redesign pages.

Do not rename menus.

Do not change navigation unless requested.

---

# Documentation Rules

Whenever architecture changes:

Update

PROJECT-BIBLE.md

PROJECT-ARCHITECTURE.md

CHANGELOG.md

SPRINT-LOG.md

---

# Testing Rules

When implementation changes:

Run

git diff --check

Run

php artisan test

If available:

php artisan view:cache

Report results.

---

# Security Rules

Never commit:

Passwords

API Keys

Government IDs

Real employee documents

Sensitive files

Never bypass validation.

Never disable foreign keys.

Never disable transactions.

---

# Forbidden Actions

Never delete migrations.

Never drop production tables.

Never remove tests.

Never replace architecture.

Never modify unrelated modules.

Never rewrite working code without reason.

---

# Definition of Done

A task is complete only if:

✓ Requirements implemented

✓ Tests passed

✓ No unrelated files changed

✓ Documentation updated

✓ Existing architecture preserved

✓ Summary provided

---

# AI Completion Format

Every completed task must include:

1. Files Changed

2. Architecture Decision

3. Verification

4. Remaining Work

5. Risks (if any)

6. Summary

---

End of Document