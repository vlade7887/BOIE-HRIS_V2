<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Base extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'base_code',
        'base_name',
        'remarks',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function units()
    {
        return $this->hasMany(Unit::class);
    }
}
