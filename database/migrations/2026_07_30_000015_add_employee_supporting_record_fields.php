<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_contacts', function (Blueprint $table) {
            $table->string('alternate_mobile_number', 20)->nullable()->after('mobile_number');
        });

        Schema::table('employee_addresses', function (Blueprint $table) {
            $table->string('present_house_number', 100)->nullable()->after('employee_id');
            $table->string('present_street', 150)->nullable();
            $table->string('present_barangay', 150)->nullable();
            $table->string('present_city', 150)->nullable();
            $table->string('present_province', 150)->nullable();
            $table->string('present_zip_code', 20)->nullable();
            $table->string('permanent_house_number', 100)->nullable();
            $table->string('permanent_street', 150)->nullable();
            $table->string('permanent_barangay', 150)->nullable();
            $table->string('permanent_city', 150)->nullable();
            $table->string('permanent_province', 150)->nullable();
            $table->string('permanent_zip_code', 20)->nullable();
        });

        Schema::table('employee_government_ids', function (Blueprint $table) {
            $table->string('passport_number', 50)->nullable()->after('tin_number');
            $table->string('driver_license_number', 50)->nullable()->after('passport_number');
        });
    }

    public function down(): void
    {
        Schema::table('employee_government_ids', function (Blueprint $table) {
            $table->dropColumn(['passport_number', 'driver_license_number']);
        });

        Schema::table('employee_addresses', function (Blueprint $table) {
            $table->dropColumn([
                'present_house_number', 'present_street', 'present_barangay', 'present_city', 'present_province', 'present_zip_code',
                'permanent_house_number', 'permanent_street', 'permanent_barangay', 'permanent_city', 'permanent_province', 'permanent_zip_code',
            ]);
        });

        Schema::table('employee_contacts', function (Blueprint $table) {
            $table->dropColumn('alternate_mobile_number');
        });
    }
};
