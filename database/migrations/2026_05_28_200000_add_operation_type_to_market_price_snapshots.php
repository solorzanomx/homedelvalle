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

        // 2. Reemplazar índice — solo en MySQL (SQLite gestiona índices de forma diferente)
        if (DB::getDriverName() === 'mysql') {
            DB::statement('DROP INDEX IF EXISTS mps_colonia_type_age_period_idx ON market_price_snapshots');
            DB::statement('CREATE INDEX mps_main_idx ON market_price_snapshots (market_colonia_id, operation_type, property_type, age_category, period)');
        }
    }

    public function down(): void
    {
        Schema::table('market_price_snapshots', function (Blueprint $table) {
            $table->dropIndex('mps_main_idx');
            $table->index(
                ['market_colonia_id', 'property_type', 'age_category', 'period'],
                'mps_colonia_type_age_period_idx'
            );
            $table->dropColumn('operation_type');
        });
    }
};
