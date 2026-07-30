<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'employee_no',
        'biometric_id',
        'last_name',
        'first_name',
        'middle_name',
        'suffix',
        'nickname',
        'gender',
        'civil_status',
        'birth_date',
        'birth_place',
        'nationality',
        'religion',
        'blood_type',
        'profile_photo',
        'company_id',
        'base_id',
        'unit_id',
        'department_id',
        'section_id',
        'position_id',
        'employment_status_id',
        'employee_class_id',
        'date_hired',
        'date_regularized',
        'date_resigned',
        'employment_end_date',
        'immediate_supervisor_id',
        'department_head_id',
        'remarks',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'date_hired' => 'date',
            'date_regularized' => 'date',
            'date_resigned' => 'date',
            'employment_end_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function base()
    {
        return $this->belongsTo(Base::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function employmentStatus()
    {
        return $this->belongsTo(EmploymentStatus::class);
    }

    public function employeeClass()
    {
        return $this->belongsTo(EmployeeClass::class);
    }

    public function immediateSupervisor()
    {
        return $this->belongsTo(Employee::class, 'immediate_supervisor_id');
    }

    public function departmentHead()
    {
        return $this->belongsTo(Employee::class, 'department_head_id');
    }

    public function employeeContact()
    {
        return $this->hasOne(EmployeeContact::class);
    }

    public function employeeAddress()
    {
        return $this->hasOne(EmployeeAddress::class);
    }

    public function employeeGovernmentId()
    {
        return $this->hasOne(EmployeeGovernmentId::class);
    }

    public function employeeEmergencyContacts()
    {
        return $this->hasMany(EmployeeEmergencyContact::class);
    }

    public function employeeDocuments()
    {
        return $this->hasMany(EmployeeDocument::class);
    }
}
