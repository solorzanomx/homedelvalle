<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fase 1-A: Agrega intent + metadata a operations.
 * intent: distingue captaciones de renta ('renta') vs. venta ('venta').
 * metadata: JSON libre para workflows, portal y automations.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('operations', function (Blueprint $table) {
            if (! Schema::hasColumn('operations', 'intent')) {
                $table->string('intent', 20)
                    ->nullable()
                    ->after('type')
                    ->comment('venta | renta — intención de la operación');
            }
            if (! Schema::hasColumn('operations', 'metadata')) {
                $table->json('metadata')
                    ->nullable()
                    ->after('notes')
                    ->comment('Datos adicionales JSON para workflows y portal');
            }
        });
    }

    public function down(): void
    {
        Schema::table('operations', function (Blueprint $table) {
            $cols = array_filter(['intent', 'metadata'], fn($c) => Schema::hasColumn('operations', $c));
            if ($cols) $table->dropColumn(array_values($cols));
        });
    }
};
