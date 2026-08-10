<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_delegations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('acting_for_employee_id')
                ->constrained('employees')
                ->restrictOnDelete();

            $table->foreignId('delegate_employee_id')
                ->constrained('employees')
                ->restrictOnDelete();

            $table->date('effective_from');
            $table->date('effective_until');

            $table->text('reason');

            $table->string('status', 20)->default('active');

            $table->timestamp('revoked_at')->nullable();

            $table->foreignId('revoked_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(
                [
                    'acting_for_employee_id',
                    'status',
                    'effective_from',
                    'effective_until',
                ],
                'approval_delegations_acting_for_dates_index'
            );

            $table->index(
                ['delegate_employee_id', 'status'],
                'approval_delegations_delegate_status_index'
            );

            $table->index('revoked_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_delegations');
    }
};