<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeGovernmentId extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'employee_id',
        'sss_number',
        'philhealth_number',
        'pagibig_number',
        'tin_number',
        'passport_number',
        'driver_license_number',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
