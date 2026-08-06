<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            // "Codigo de aviso" de Inmuebles24 (unico por publicacion, ej.
            // "149911364") — Inmuebles24 no tiene API, este campo se llena
            // a mano y sirve para vincular leads entrantes por correo a la
            // Property correcta, igual que easybroker_id hace con la API.
            $table->string('inmuebles24_ad_code')->nullable()->after('easybroker_public_url')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn('inmuebles24_ad_code');
        });
    }
};
