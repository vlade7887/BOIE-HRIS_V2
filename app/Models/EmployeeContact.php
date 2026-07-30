<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeContact extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'employee_id',
        'mobile_number',
        'alternate_mobile_number',
        'telephone_number',
        'company_email',
        'personal_email',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
