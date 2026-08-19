<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Holiday extends Model
{
    use SoftDeletes;

    public const TYPE_REGULAR = 'regular';
    public const TYPE_SPECIAL_NON_WORKING = 'special_non_working';
    public const TYPE_OTHER = 'other';

    protected $fillable = [
        'holiday_date',
        'name',
        'holiday_type',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'holiday_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public static function types(): array
    {
        return [
            self::TYPE_REGULAR,
            self::TYPE_SPECIAL_NON_WORKING,
            self::TYPE_OTHER,
        ];
    }
}
