# BOIE HRIS Development Standards

## Framework
- Laravel 13
- PHP 8.3+
- MySQL
- Blade Templates
- Bootstrap 5

## Controllers
- Use Resource Controllers.
- Return Blade views.
- Do not return JSON responses unless creating an API.

## Validation
- Use Form Request classes.
- Keep validation rules inside Form Requests.

## Models
- Use protected $fillable.
- Use SoftDeletes where applicable.
- Use casts() for boolean fields.

## Database
- Primary key: id
- Foreign keys: *_id
- Soft deletes for master tables.
- Use timestamps.

## Naming
- Tables: plural (companies, bases, departments)
- Models: singular (Company, Base, Department)
- Controllers: singular (CompanyController)

## UI
- Blade templates
- Bootstrap 5
- Resource routes

## Documentation
- Every module must have its own Specification document in the docs folder.