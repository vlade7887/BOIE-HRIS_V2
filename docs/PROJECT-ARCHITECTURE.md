# BOIE HRIS - Project Architecture

Version: 1.0

Status: Active

---

# 1. Overview

BOIE HRIS follows a modular Laravel architecture using the MVC pattern with a Service Layer.

The objective is to keep business logic separated from controllers, making the application easier to maintain, test, and expand.

---

# 2. Technology Stack

Framework

Laravel 13

Language

PHP 8.3

Frontend

Bootstrap 5

AdminLTE

Database

MySQL

Architecture Pattern

MVC + Service Layer

---

# 3. Request Flow

Browser

↓

Route

↓

Controller

↓

Form Request

↓

Service

↓

Eloquent Model

↓

Database

↓

Response

↓

Blade View

---

# 4. Responsibilities

## Controller

Responsible for:

- Receiving requests
- Returning views
- Redirects
- Calling Services

Controllers must remain thin.

Controllers must never contain complex business logic.

---

## Form Request

Responsible for:

- Validation
- Authorization
- Sanitizing request data when applicable

Never validate inside Controllers.

---

## Service Layer

Responsible for:

- Business rules
- Transactions
- Multi-table operations
- Reusable workflows

Examples

EmployeeService

MasterDataImportService

AttendanceService

PayrollService

---

## Models

Responsible only for:

- Database relationships
- Attribute casting
- Scopes
- Accessors & Mutators

Models should not contain large business workflows.

---

## Blade Views

Responsible only for presentation.

Never place business logic inside Blade templates.

---

# 5. Folder Structure

app/

Controllers

Models

Services

Http

Requests

Policies

Observers

database/

migrations

seeders

factories

resources/

views

layouts

employees

administration

attendance

leave

payroll

reports

routes/

web.php

---

# 6. Application Layers

Presentation Layer

- Blade
- Bootstrap
- AdminLTE

Application Layer

- Controllers
- Form Requests
- Services

Domain Layer

- Business Logic
- Validation Rules
- Transactions

Persistence Layer

- Eloquent Models
- MySQL


# 7. Module Architecture

Employee

↓

Contact

↓

Address

↓

Government IDs

↓

Emergency Contacts

↓

Documents

Each module owns its own Models, Requests, Services, and Views.

---

# 8. Database Design Principles

Use foreign keys.

Use lookup tables.

Normalize related data.

Avoid duplicated data.

Use timestamps.

Use soft deletes where appropriate.

---

# 9. Service Layer Rules

Business logic belongs inside Services.

Examples

EmployeeService

AttendanceService

LeaveService

PayrollService

MasterDataImportService

Never duplicate business logic across Controllers.

---

# 10. Transactions

Whenever multiple related tables are modified:

Always use:

DB::transaction()

Example

Employee

↓

EmployeeContact

↓

EmployeeAddress

↓

Government IDs

All records must succeed together or rollback together.

---

# 11. Validation

Always use Form Requests.

StoreEmployeeRequest

UpdateEmployeeRequest

StoreCompanyRequest

UpdateCompanyRequest

Never validate inside Controllers.

---

# 12. Lookup Tables

Lookup data must come from database tables.

Never hardcode values.

Examples

Company

Base

Unit

Department

Section

Position

Employment Status

Employee Class

---

# 13. Future Modules

Attendance

Leave

Payroll

Reports

Recruitment

Performance Evaluation

Employee Self-Service

Every future module must follow this architecture.

---

# 14. Testing

Every completed feature should include:

Validation testing

Feature testing

Database integrity checks

Regression testing

---

# 15. Documentation

Whenever architecture changes, update:

PROJECT-ARCHITECTURE.md

PROJECT-BIBLE.md

CHANGELOG.md

SPRINT-LOG.md

---

End of Document