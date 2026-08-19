<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_types', function (Blueprint $table) {
            $table->string('filing_timing', 30)->default('same_day')->after('requires_attachment');
            $table->unsignedSmallInteger('minimum_advance_days')->default(0)->after('filing_timing');
            $table->string('carryover_policy', 30)->default('expire')->after('minimum_advance_days');
            $table->unsignedSmallInteger('carryover_grace_days')->default(0)->after('carryover_policy');
            $table->index('filing_timing');
        });
    }

    public function down(): void
    {
        Schema::table('leave_types', function (Blueprint $table) {
            $table->dropIndex(['filing_timing']);
            $table->dropColumn(['filing_timing', 'minimum_advance_days', 'carryover_policy', 'carryover_grace_days']);
        });
    }
};
