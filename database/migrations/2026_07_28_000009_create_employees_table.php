<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_no', 20)->unique();
            $table->string('biometric_id', 30)->nullable()->unique();
            $table->string('last_name', 100);
            $table->string('first_name', 100);
            $table->string('middle_name', 100)->nullable();
            $table->string('suffix', 20)->nullable();
            $table->string('nickname', 100)->nullable();
            $table->string('gender', 20);
            $table->string('civil_status', 30);
            $table->date('birth_date');
            $table->string('birth_place', 150)->nullable();
            $table->string('nationality', 100)->default('Filipino');
            $table->string('religion', 100)->nullable();
            $table->string('blood_type', 10)->nullable();
            $table->string('profile_photo')->nullable();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('base_id')->constrained('bases')->restrictOnDelete();
            $table->foreignId('unit_id')->constrained('units')->restrictOnDelete();
            $table->foreignId('department_id')->constrained('departments')->restrictOnDelete();
            $table->foreignId('section_id')->constrained('sections')->restrictOnDelete();
            $table->foreignId('position_id')->constrained('positions')->restrictOnDelete();
            $table->foreignId('employment_status_id')->constrained('employment_statuses')->restrictOnDelete();
            $table->foreignId('employee_class_id')->constrained('employee_classes')->restrictOnDelete();
            $table->date('date_hired');
            $table->date('date_regularized')->nullable();
            $table->date('date_resigned')->nullable();
            $table->date('employment_end_date')->nullable();
            $table->foreignId('immediate_supervisor_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('department_head_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->text('remarks')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
