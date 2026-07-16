<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * La API real de EasyBroker ubica por nombre de catálogo (full_name, ej.
 * "Del Valle Norte, Benito Juárez, Ciudad de México"), no por IDs numéricos.
 * default_city_id / default_admin_division_id (inventados en abril) quedan
 * huérfanos — no se borran por si hay datos, pero ya nada los usa.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('easybroker_settings', function (Blueprint $table) {
            $table->string('default_location_name')->nullable()->after('default_admin_division_id');
        });
    }

    public function down(): void
    {
        Schema::table('easybroker_settings', function (Blueprint $table) {
            $table->dropColumn('default_location_name');
        });
    }
};
