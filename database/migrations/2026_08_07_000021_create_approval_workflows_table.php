<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_workflows', function (Blueprint $table) {
            $table->id();

            $table->string('code', 50);
            $table->unsignedSmallInteger('version')->default(1);

            $table->string('name', 150);
            $table->text('description')->nullable();

            $table->string('status', 20)->default('draft');

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['code', 'version']);

            $table->index('status');
            $table->index('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_workflows');
    }
};