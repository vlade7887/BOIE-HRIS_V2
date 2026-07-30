# BOIE HRIS - Database Standards

Version: 1.0

Status: Active

---

# 1. Purpose

This document defines the database design standards for BOIE HRIS.

Every database table, migration, model, and relationship must follow these standards.

---

# 2. Database Engine

Database

MySQL

Character Set

utf8mb4

Collation

utf8mb4_unicode_ci

---

# 3. Naming Standards

Tables

Plural

Examples

employees

employee_contacts

employee_addresses

employee_government_ids

companies

departments

positions

Models

Singular

Employee

EmployeeContact

Department

Position

Columns

snake_case

Examples

employee_number

date_hired

employment_status_id

created_at

updated_at

---

# 4. Primary Keys

Every table must use:

id

Unsigned Big Integer

Auto Increment

Never use business codes as primary keys.

---

# 5. Foreign Keys

Always use:

table_name_id

Examples

company_id

department_id

position_id

employee_id

Always define foreign key constraints.

---

# 6. Lookup Tables

Store configurable values in lookup tables.

Examples

companies

bases

units

departments

sections

positions

employment_statuses

employee_classes

Never hardcode lookup values.

---

# 7. Required Columns

Every table should include:

created_at

updated_at

Soft deletes where appropriate.

---

# 8. Soft Deletes

Use soft deletes for master data and employee-related records whenever recovery may be required.

Avoid permanent deletion unless specifically approved.

---

# 9. Relationships

Prefer Eloquent relationships.

Examples

Employee

hasOne Contact

hasOne Address

hasOne GovernmentId

belongsTo Company

belongsTo Department

belongsTo Position

Never manually manage relationships when Eloquent methods can be used.

---

# 10. Indexes

Create indexes for:

Foreign keys

Employee Number

Email

Frequently searched fields

Use unique indexes where appropriate.

---
# 11. Lookup Table Standards

Every lookup table should contain:

- id
- code
- name
- description (nullable)
- is_active
- created_at
- updated_at
- deleted_at (when Soft Deletes are used)

Lookup tables should only store master data.

Lookup tables must never store transactional data.

All lookup tables should support future expansion without changing application code.


# 12. Transactions

Whenever multiple tables are modified:

Always use:

DB::transaction()

Example

Employee

↓

Employee Contact

↓

Employee Address

↓

Government IDs

Either all succeed or all rollback.

---

# 13. Migrations

Never modify an existing migration after it has been applied.

Always create a new migration.

Migration names should clearly describe the change.

Examples

create_employees_table

add_passport_to_employee_government_ids_table

create_employee_documents_table

---

# 14. Seeders

Use seeders only for:

Master Data

Development Data

Testing Data

Do not seed confidential employee information into production.

---

# 15. Data Integrity

Use:

Foreign Keys

Unique Constraints

Validation

Database Constraints

Avoid duplicate records.

---

# 16. Audit Rules

Record:

created_at

updated_at

deleted_at (if soft deletes are used)

Future versions may include audit logs.

---

# 17. Performance

Avoid N+1 queries.

Use eager loading.

Index frequently searched columns.

Avoid unnecessary joins.

---

# 18. Security

Never store passwords in plain text.

Protect government IDs.

Protect employee personal data.

Never expose confidential information through debug output.

---

# 19. Future Database Modules

Attendance

Leave

Payroll

Reports

Recruitment

Performance

Training

Assets

These modules must follow the same database standards.

---

End of Document