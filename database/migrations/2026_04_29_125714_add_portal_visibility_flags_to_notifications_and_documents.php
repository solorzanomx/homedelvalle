<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    /**
     * Fase 1-D: Agrega portal_visible a notifications y documents.
     * Controla qué notificaciones y documentos aparecen en miportal.homedelvalle.mx.
     */
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            if (! Schema::hasColumn('notifications', 'portal_visible')) {
                $table->boolean('portal_visible')
                    ->default(false)
                    ->after('read_at')
                    ->comment('¿Visible en el portal del cliente?');
            }
            if (! Schema::hasColumn('notifications', 'client_id')) {
                // Sin constrained() para evitar conflictos de FK en SQLite local.
                // En MySQL la FK se aplica por convención de nombre (clients.id).
                $table->unsignedBigInteger('client_id')
                    ->nullable()
                    ->after('portal_visible')
                    ->comment('Cliente al que va dirigida esta notificación');
            }
        });

        Schema::table('documents', function (Blueprint $table) {
            if (! Schema::hasColumn('documents', 'portal_visible')) {
                $table->boolean('portal_visible')
                    ->default(true)
                    ->after('is_captacion_required')
                    ->comment('¿Visible en el portal del cliente?');
            }
            if (! Schema::hasColumn('documents', 'portal_category')) {
                $table->string('portal_category', 50)
                    ->nullable()
                    ->after('portal_visible')
                    ->comment('Categoría para el portal: contrato, recibo, identificacion, otro');
            }
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $cols = array_filter(['portal_visible', 'client_id'], fn($c) => Schema::hasColumn('notifications', $c));
            if ($cols) $table->dropColumn(array_values($cols));
        });

        Schema::table('documents', function (Blueprint $table) {
            $cols = array_filter(['portal_visible', 'portal_category'], fn($c) => Schema::hasColumn('documents', $c));
            if ($cols) $table->dropColumn(array_values($cols));
        });
    }
};
