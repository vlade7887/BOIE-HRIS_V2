<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('leave_types')->where('code', 'VL')->update([
            'filing_timing' => 'advance',
            'minimum_advance_days' => 3,
        ]);

        DB::table('leave_types')->where('code', 'SL')->update([
            'filing_timing' => 'after_return',
            'minimum_advance_days' => 0,
        ]);

        DB::table('leave_types')->where('code', 'EL')->update([
            'filing_timing' => 'same_day',
            'minimum_advance_days' => 0,
        ]);
    }

    public function down(): void
    {
        DB::table('leave_types')->whereIn('code', ['VL', 'SL', 'EL'])->update([
            'filing_timing' => 'same_day',
            'minimum_advance_days' => 0,
        ]);
    }
};
