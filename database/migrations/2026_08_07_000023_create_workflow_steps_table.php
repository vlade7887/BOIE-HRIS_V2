<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_steps', function (Blueprint $table) {
            $table->id();

            $table->foreignId('approval_workflow_id')
                ->constrained('approval_workflows')
                ->restrictOnDelete();

            $table->unsignedSmallInteger('step_order');

            $table->string('step_name', 150);

            $table->foreignId('approver_employee_id')
                ->constrained('employees')
                ->restrictOnDelete();

            $table->boolean('is_hr_step')->default(false);

            $table->text('description')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(
                ['approval_workflow_id', 'step_order'],
                'workflow_steps_workflow_order_index'
            );

            $table->index('approver_employee_id');
            $table->index('is_hr_step');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_steps');
    }
};