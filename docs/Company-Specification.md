# BOIE HRIS
# Company Module Specification v1.0

## Module
Company

## Purpose
Stores all companies handled by the HRIS.

---

## Database Table

companies

| Field | Type | Required | Notes |
|-------|------|----------|------|
| id | bigint | Yes | Primary Key |
| company_code | varchar(20) | Yes | Unique |
| company_name | varchar(150) | Yes | Company Name |
| contact_person | varchar(150) | No | Contact Person |
| contact_number | varchar(30) | No | Contact Number |
| email | varchar(150) | No | Company Email |
| address | text | No | Company Address |
| remarks | text | No | Notes |
| is_active | boolean | Yes | Default True |
| created_at | timestamp | Yes | Laravel Timestamp |
| updated_at | timestamp | Yes | Laravel Timestamp |
| deleted_at | timestamp | No | Soft Delete |

---

## Validation

Company Code
- Required
- Unique
- Maximum 20 characters

Company Name
- Required
- Maximum 150 characters

Contact Person
- Optional
- Maximum 150 characters

Contact Number
- Optional
- Maximum 30 characters

Email
- Optional
- Valid Email
- Maximum 150 characters

Remarks
- Optional

---

## Features

- Add Company
- Edit Company
- Archive Company
- Restore Company
- Search Company

---

## Coding Standards

- Laravel 13
- Form Request Validation
- Soft Deletes
- Eloquent ORM
- Blade Views
- Bootstrap 5
- Resource Controller

---

## Future

- Activity Logs
- Created By
- Updated By
- Deleted By