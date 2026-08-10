<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_request_steps', function (Blueprint $table) {
            $table->id();

            $table->foreignId('approval_request_id')
                ->constrained('approval_requests')
                ->restrictOnDelete();

            $table->unsignedSmallInteger('step_order');

            $table->foreignId('canonical_approver_employee_id')
                ->constrained('employees')
                ->restrictOnDelete();

            $table->string('step_type', 30)->default('selected');
            $table->string('status', 20)->default('waiting');
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('acted_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->timestamps();

            $table->unique(
                ['approval_request_id', 'step_order'],
                'approval_request_steps_request_order_unique'
            );
            $table->unique(
                ['approval_request_id', 'canonical_approver_employee_id'],
                'approval_request_steps_request_approver_unique'
            );
            $table->index(['approval_request_id', 'status']);
            $table->index(
                ['canonical_approver_employee_id', 'status'],
                'approval_steps_approver_status_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_request_steps');
    }
};
