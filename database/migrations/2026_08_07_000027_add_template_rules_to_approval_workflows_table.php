<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('approval_workflows', function (Blueprint $table) {
            $table->string('module_key', 50)->nullable()->after('description');
            $table->unsignedSmallInteger('min_approvers')->default(1)->after('module_key');
            $table->unsignedSmallInteger('max_approvers')->default(5)->after('min_approvers');
            $table->boolean('hr_final_required')->default(true)->after('max_approvers');
            $table->foreignId('hr_final_approver_employee_id')
                ->nullable()
                ->after('hr_final_required')
                ->constrained('employees')
                ->restrictOnDelete();

            $table->index('module_key');
        });
    }

    public function down(): void
    {
        Schema::table('approval_workflows', function (Blueprint $table) {
            $table->dropForeign(['hr_final_approver_employee_id']);
            $table->dropIndex(['module_key']);
            $table->dropColumn([
                'module_key',
                'min_approvers',
                'max_approvers',
                'hr_final_required',
                'hr_final_approver_employee_id',
            ]);
        });
    }
};
