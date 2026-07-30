<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeEmergencyContact extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'employee_id',
        'contact_name',
        'relationship',
        'mobile_number',
        'telephone_number',
        'address',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
