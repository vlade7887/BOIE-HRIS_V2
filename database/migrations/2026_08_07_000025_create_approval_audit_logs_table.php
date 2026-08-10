<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_audit_logs', function (Blueprint $table) {
            $table->id();

            $table->string('event_type', 100);

            $table->string('auditable_type', 150)->nullable();
            $table->unsignedBigInteger('auditable_id')->nullable();

            $table->foreignId('actor_user_id')
                ->nullable()
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('actor_employee_id')
                ->nullable()
                ->constrained('employees')
                ->restrictOnDelete();

            $table->uuid('correlation_id')->nullable();

            $table->json('metadata')->nullable();

            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamp('occurred_at');

            $table->timestamp('created_at');

            $table->index(
                ['auditable_type', 'auditable_id'],
                'approval_audit_logs_auditable_index'
            );

            $table->index(
                ['actor_user_id', 'occurred_at'],
                'approval_audit_logs_user_occurred_index'
            );

            $table->index(
                ['actor_employee_id', 'occurred_at'],
                'approval_audit_logs_employee_occurred_index'
            );

            $table->index(
                ['event_type', 'occurred_at'],
                'approval_audit_logs_event_occurred_index'
            );

            $table->index('correlation_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_audit_logs');
    }
};