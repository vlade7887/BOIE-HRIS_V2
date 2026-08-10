<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_assignments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('approval_workflow_id')
                ->constrained('approval_workflows')
                ->restrictOnDelete();

            $table->foreignId('employee_id')
                ->constrained('employees')
                ->restrictOnDelete();

            $table->string('module_key', 50);

            $table->date('effective_from');
            $table->date('effective_until')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index([
                'employee_id',
                'module_key',
                'effective_from',
                'effective_until',
            ], 'workflow_assignments_employee_module_dates_index');

            $table->index([
                'approval_workflow_id',
                'module_key',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_assignments');
    }
};