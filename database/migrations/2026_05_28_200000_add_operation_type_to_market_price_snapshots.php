<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Agregar operation_type (todos los existentes son 'sale')
        if (!Schema::hasColumn('market_price_snapshots', 'operation_type')) {
            Schema::table('market_price_snapshots', function (Blueprint $table) {
                $table->enum('operation_type', ['sale', 'rent'])
                      ->default('sale')
                      ->after('market_colonia_id');
            });
        }

        // 2. Agregar nuevo índice que incluye operation_type (sin tocar el viejo — tiene FK)
        if (DB::getDriverName() === 'mysql') {
            // Crear solo si no existe ya
            $indexExists = collect(DB::select("SHOW INDEX FROM market_price_snapshots WHERE Key_name = 'mps_main_idx'"))->isNotEmpty();
            if (!$indexExists) {
                DB::statement('CREATE INDEX mps_main_idx ON market_price_snapshots (market_colonia_id, operation_type, property_type, age_category, period)');
            }
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            // Eliminar índice nuevo si existe
            $hasMain = collect(DB::select("SHOW INDEX FROM market_price_snapshots WHERE Key_name = 'mps_main_idx'"))->isNotEmpty();
            if ($hasMain) {
                DB::statement('DROP INDEX mps_main_idx ON market_price_snapshots');
            }
            // Recrear índice viejo solo si no existe ya
            $hasOld = collect(DB::select("SHOW INDEX FROM market_price_snapshots WHERE Key_name = 'mps_colonia_type_age_period_idx'"))->isNotEmpty();
            if (!$hasOld) {
                DB::statement('CREATE INDEX mps_colonia_type_age_period_idx ON market_price_snapshots (market_colonia_id, property_type, age_category, period)');
            }
        }

        Schema::table('market_price_snapshots', function (Blueprint $table) {
            $table->dropColumn('operation_type');
        });
    }
};
