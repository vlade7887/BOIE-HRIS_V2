<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            Schema::table('employees', function (Blueprint $table) {
                $table->string('nationality', 100)->default('Filipino')->change();
            });
        } else {
            DB::statement("ALTER TABLE employees MODIFY nationality VARCHAR(100) NOT NULL DEFAULT 'Filipino'");
        }

        DB::table('employees')
            ->whereNull('nationality')
            ->orWhere('nationality', '')
            ->orWhereRaw('TRIM(nationality) = ""')
            ->update(['nationality' => 'Filipino']);
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            Schema::table('employees', function (Blueprint $table) {
                $table->string('nationality', 100)->nullable()->default(null)->change();
            });
        } else {
            DB::statement('ALTER TABLE employees MODIFY nationality VARCHAR(100) NULL DEFAULT NULL');
        }
    }
};
