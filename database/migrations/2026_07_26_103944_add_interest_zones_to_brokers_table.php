<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Zonas donde trabaja el broker externo — capturadas en la verificación de
 * leads posibles-broker de EasyBroker (mismo patrón que interest_zones en
 * developer_contacts), para poder segmentar el envío masivo cuando una
 * propiedad se atora.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('brokers', function (Blueprint $table) {
            $table->json('interest_zones')->nullable()->after('specialty');
        });
    }

    public function down(): void
    {
        Schema::table('brokers', function (Blueprint $table) {
            $table->dropColumn('interest_zones');
        });
    }
};
