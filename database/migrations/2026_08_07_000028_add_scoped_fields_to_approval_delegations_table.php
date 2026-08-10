<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('approval_delegations', function (Blueprint $table) {
            $table->string('scope_type', 30)->default('all')->after('reason');
            $table->foreignId('department_id')
                ->nullable()
                ->after('scope_type')
                ->constrained('departments')
                ->restrictOnDelete();

            $table->index(['scope_type', 'department_id']);
        });
    }

    public function down(): void
    {
        Schema::table('approval_delegations', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropIndex(['scope_type', 'department_id']);
            $table->dropColumn(['scope_type', 'department_id']);
        });
    }
};
