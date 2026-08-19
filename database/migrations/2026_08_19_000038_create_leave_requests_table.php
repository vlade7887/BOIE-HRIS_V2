<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->restrictOnDelete();
            $table->foreignId('leave_type_id')->constrained('leave_types')->restrictOnDelete();
            $table->string('leave_type_code_snapshot', 20);
            $table->string('leave_type_name_snapshot', 150);
            $table->foreignId('department_id')->nullable()->constrained('departments')->restrictOnDelete();
            $table->string('department_code_snapshot', 20)->nullable();
            $table->string('department_name_snapshot', 150)->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('total_units', 8, 2)->default(0);
            $table->text('reason')->nullable();
            $table->date('returned_to_work_date')->nullable();
            $table->string('status', 20)->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['employee_id', 'status']);
            $table->index(['employee_id', 'start_date', 'end_date']);
            $table->index('department_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
    }
};
