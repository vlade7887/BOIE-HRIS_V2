<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_request_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('leave_request_id')->constrained('leave_requests')->restrictOnDelete();
            $table->date('leave_date');
            $table->boolean('is_weekend');
            $table->boolean('is_holiday');
            $table->foreignId('holiday_id')->nullable()->constrained('holidays')->restrictOnDelete();
            $table->string('holiday_name_snapshot', 150)->nullable();
            $table->boolean('is_working_day');
            $table->decimal('requested_unit', 3, 2);
            $table->string('half_day_period', 3)->nullable();
            $table->decimal('counted_units', 3, 2);
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['leave_request_id', 'leave_date'], 'leave_request_days_request_date_unique');
            $table->index('leave_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_request_days');
    }
};
