<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_balance_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('leave_request_id')->constrained('leave_requests')->restrictOnDelete();
            $table->foreignId('leave_entitlement_id')->constrained('leave_entitlements')->restrictOnDelete();
            $table->decimal('reserved_days', 8, 2);
            $table->string('status', 20)->default('reserved');
            $table->timestamp('released_at')->nullable();
            $table->timestamp('consumed_at')->nullable();
            $table->timestamps();

            $table->unique(['leave_request_id', 'leave_entitlement_id'], 'leave_reservations_request_entitlement_unique');
            $table->index(['leave_entitlement_id', 'status']);
            $table->index(['leave_request_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_balance_reservations');
    }
};
