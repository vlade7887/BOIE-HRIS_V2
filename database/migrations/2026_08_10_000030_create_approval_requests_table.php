<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('requester_employee_id')
                ->constrained('employees')
                ->restrictOnDelete();

            $table->string('module_key', 50);
            $table->string('approvable_type', 150);
            $table->unsignedBigInteger('approvable_id');

            $table->foreignId('approval_workflow_id')
                ->nullable()
                ->constrained('approval_workflows')
                ->restrictOnDelete();

            $table->string('workflow_code', 50)->nullable();
            $table->unsignedSmallInteger('workflow_version')->nullable();
            $table->string('workflow_name', 150)->nullable();

            $table->foreignId('request_department_id')
                ->nullable()
                ->constrained('departments')
                ->restrictOnDelete();

            $table->string('status', 20)->default('draft');
            $table->unsignedSmallInteger('current_step_order')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->timestamps();

            $table->unique(
                ['approvable_type', 'approvable_id'],
                'approval_requests_approvable_unique'
            );

            $table->index(
                ['requester_employee_id', 'status', 'created_at'],
                'approval_requests_requester_status_created_index'
            );
            $table->index(['module_key', 'status']);
            $table->index(['request_department_id', 'status']);
            $table->index(['status', 'current_step_order']);
            $table->index('approval_workflow_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_requests');
    }
};
