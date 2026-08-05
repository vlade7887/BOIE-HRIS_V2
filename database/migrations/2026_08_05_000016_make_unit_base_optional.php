<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->foreignId('base_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        if (DB::table('units')->whereNull('base_id')->exists()) {
            throw new \RuntimeException(
                'Cannot make units.base_id required because Units without a Base currently exist.'
            );
        }

        Schema::table('units', function (Blueprint $table) {
            $table->foreignId('base_id')->nullable(false)->change();
        });
    }
};