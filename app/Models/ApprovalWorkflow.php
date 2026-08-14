<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApprovalWorkflow extends Model
{
    use SoftDeletes;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_ARCHIVED = 'archived';

    public const MODULE_KEYS = [
        'approval_demo',
        'leave',
        'overtime',
        'official_business',
        'undertime',
    ];

    protected $fillable = [
        'code',
        'version',
        'name',
        'description',
        'module_key',
        'min_approvers',
        'max_approvers',
        'hr_final_required',
        'hr_final_approver_employee_id',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'version' => 'integer',
            'min_approvers' => 'integer',
            'max_approvers' => 'integer',
            'hr_final_required' => 'boolean',
        ];
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_DRAFT,
            self::STATUS_ACTIVE,
            self::STATUS_INACTIVE,
            self::STATUS_ARCHIVED,
        ];
    }

    public static function moduleKeys(): array
    {
        return self::MODULE_KEYS;
    }

    public function hrFinalApprover(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'hr_final_approver_employee_id');
    }
}
