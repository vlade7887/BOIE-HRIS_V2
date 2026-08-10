<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('workflow_assignments') && ! Schema::hasTable('legacy_workflow_assignments')) {
            Schema::rename('workflow_assignments', 'legacy_workflow_assignments');
        }

        if (Schema::hasTable('workflow_steps') && ! Schema::hasTable('legacy_workflow_steps')) {
            Schema::rename('workflow_steps', 'legacy_workflow_steps');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('legacy_workflow_assignments') && ! Schema::hasTable('workflow_assignments')) {
            Schema::rename('legacy_workflow_assignments', 'workflow_assignments');
        }

        if (Schema::hasTable('legacy_workflow_steps') && ! Schema::hasTable('workflow_steps')) {
            Schema::rename('legacy_workflow_steps', 'workflow_steps');
        }
    }
};
