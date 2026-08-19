<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_balance_ledger', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->restrictOnDelete();
            $table->foreignId('leave_type_id')->constrained('leave_types')->restrictOnDelete();
            $table->foreignId('leave_entitlement_id')->nullable()->constrained('leave_entitlements')->restrictOnDelete();
            $table->foreignId('leave_request_id')->nullable()->constrained('leave_requests')->restrictOnDelete();
            $table->string('transaction_type', 20);
            $table->decimal('units', 8, 2);
            $table->string('reference_key', 180)->nullable()->unique();
            $table->date('effective_date');
            $table->json('metadata')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['employee_id', 'leave_type_id', 'effective_date'], 'leave_ledger_employee_type_date_index');
            $table->index(['leave_entitlement_id', 'effective_date'], 'leave_ledger_entitlement_date_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_balance_ledger');
    }
};
