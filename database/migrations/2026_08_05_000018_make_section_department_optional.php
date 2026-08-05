<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sections', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        if (DB::table('sections')->whereNull('department_id')->exists()) {
            throw new \RuntimeException(
                'Cannot make sections.department_id required because Sections without a Department currently exist.'
            );
        }

        Schema::table('sections', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable(false)->change();
        });
    }
};
