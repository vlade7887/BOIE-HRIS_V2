<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('leave_entitlements')) {
            Schema::create('leave_entitlements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('leave_entitlement_cycle_id')
                    ->constrained('leave_entitlement_cycles')
                    ->restrictOnDelete();
                $table->foreignId('leave_type_id')
                    ->constrained('leave_types')
                    ->restrictOnDelete();
                $table->decimal('granted_days', 8, 2);
                $table->decimal('reserved_days', 8, 2)->default(0);
                $table->decimal('consumed_days', 8, 2)->default(0);
                $table->decimal('expired_days', 8, 2)->default(0);
                $table->decimal('payout_days', 8, 2)->default(0);
                $table->timestamps();

                $table->unique(['leave_entitlement_cycle_id', 'leave_type_id'], 'leave_entitlements_cycle_type_unique');
                $table->index('leave_type_id');
            });
        } else {
            Schema::table('leave_entitlements', function (Blueprint $table) {
                $table->unique(['leave_entitlement_cycle_id', 'leave_type_id'], 'leave_entitlements_cycle_type_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_entitlements');
    }
};
