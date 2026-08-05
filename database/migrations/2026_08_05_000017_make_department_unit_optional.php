<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->foreignId('unit_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        if (DB::table('departments')->whereNull('unit_id')->exists()) {
            throw new \RuntimeException(
                'Cannot make departments.unit_id required because Departments without a Unit currently exist.'
            );
        }

        Schema::table('departments', function (Blueprint $table) {
            $table->foreignId('unit_id')->nullable(false)->change();
        });
    }
};
