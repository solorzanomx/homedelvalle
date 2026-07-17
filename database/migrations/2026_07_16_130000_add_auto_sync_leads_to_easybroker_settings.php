<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Interruptor para la sincronización automática de leads de EasyBroker.
 * Default FALSE a propósito: los portales (EasyBroker/Inmuebles24) generan
 * mucho volumen de consultas de compra/renta y Alejandro quiere probar en
 * manual antes de dejar que el scheduler llene el CRM.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('easybroker_settings', function (Blueprint $table) {
            $table->boolean('auto_sync_leads')->default(false)->after('auto_publish');
        });
    }

    public function down(): void
    {
        Schema::table('easybroker_settings', function (Blueprint $table) {
            $table->dropColumn('auto_sync_leads');
        });
    }
};
