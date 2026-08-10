<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_request_actions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('approval_request_id')
                ->constrained('approval_requests')
                ->restrictOnDelete();
            $table->foreignId('approval_request_step_id')
                ->nullable()
                ->constrained('approval_request_steps')
                ->restrictOnDelete();

            $table->string('action', 20);

            $table->foreignId('actor_user_id')
                ->nullable()
                ->constrained('users')
                ->restrictOnDelete();
            $table->foreignId('actor_employee_id')
                ->nullable()
                ->constrained('employees')
                ->restrictOnDelete();
            $table->foreignId('canonical_approver_employee_id')
                ->nullable()
                ->constrained('employees')
                ->restrictOnDelete();
            $table->foreignId('acting_for_employee_id')
                ->nullable()
                ->constrained('employees')
                ->restrictOnDelete();
            $table->foreignId('approval_delegation_id')
                ->nullable()
                ->constrained('approval_delegations')
                ->restrictOnDelete();

            $table->text('remarks')->nullable();
            $table->timestamp('acted_at');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->uuid('idempotency_key')->nullable()->unique();
            $table->timestamp('created_at');

            $table->index(['approval_request_id', 'acted_at']);
            $table->index(['approval_request_step_id', 'acted_at']);
            $table->index(['actor_user_id', 'acted_at']);
            $table->index(['actor_employee_id', 'acted_at']);
            $table->index(
                ['canonical_approver_employee_id', 'acted_at'],
                'approval_actions_canonical_acted_index'
            );
            $table->index('approval_delegation_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_request_actions');
    }
};
