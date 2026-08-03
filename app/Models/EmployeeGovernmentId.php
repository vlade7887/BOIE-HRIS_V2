<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
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

    protected function maskedSssNumber(): Attribute
    {
        return Attribute::get(fn () => $this->maskAllCharacters($this->sss_number));
    }

    protected function maskedPhilhealthNumber(): Attribute
    {
        return Attribute::get(fn () => $this->maskAllCharacters($this->philhealth_number));
    }

    protected function maskedPagibigNumber(): Attribute
    {
        return Attribute::get(fn () => $this->maskAllCharacters($this->pagibig_number));
    }

    protected function maskedTinNumber(): Attribute
    {
        return Attribute::get(fn () => $this->maskAllCharacters($this->tin_number));
    }

    protected function maskedPassportNumber(): Attribute
    {
        return Attribute::get(fn () => $this->maskExceptLastFour($this->passport_number));
    }

    protected function maskedDriverLicenseNumber(): Attribute
    {
        return Attribute::get(fn () => $this->maskExceptLastFour($this->driver_license_number));
    }

    private function maskAllCharacters(?string $value): ?string
    {
        return $value === null || $value === ''
            ? null
            : preg_replace('/[[:alnum:]]/', '*', $value);
    }

    private function maskExceptLastFour(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $visibleCharacters = 4;
        $length = strlen($value);

        if ($length <= $visibleCharacters) {
            return str_repeat('*', $length);
        }

        return str_repeat('*', $length - $visibleCharacters).substr($value, -$visibleCharacters);
    }
}
