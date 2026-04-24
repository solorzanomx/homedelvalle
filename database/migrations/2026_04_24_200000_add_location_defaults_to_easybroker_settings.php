<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('easybroker_settings', function (Blueprint $table) {
            $table->string('default_city_id')->nullable()->after('default_currency');
            $table->string('default_admin_division_id')->nullable()->after('default_city_id');
            $table->decimal('default_latitude', 10, 7)->nullable()->after('default_admin_division_id');
            $table->decimal('default_longitude', 10, 7)->nullable()->after('default_latitude');
        });

        Schema::table('properties', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->nullable()->after('zipcode');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
        });
    }

    public function down(): void
    {
        Schema::table('easybroker_settings', function (Blueprint $table) {
            $table->dropColumn(['default_city_id', 'default_admin_division_id', 'default_latitude', 'default_longitude']);
        });
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
        });
    }
};
