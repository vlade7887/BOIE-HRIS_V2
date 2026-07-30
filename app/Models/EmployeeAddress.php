<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeAddress extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'employee_id',
        'current_address',
        'permanent_address',
        'present_house_number',
        'present_street',
        'present_barangay',
        'present_city',
        'present_province',
        'present_zip_code',
        'permanent_house_number',
        'permanent_street',
        'permanent_barangay',
        'permanent_city',
        'permanent_province',
        'permanent_zip_code',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
