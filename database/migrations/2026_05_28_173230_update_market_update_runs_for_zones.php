<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            // Hacer market_colonia_id nullable (zona-level runs no tienen colonia)
            $fk = collect(DB::select("
                SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'market_update_runs'
                  AND CONSTRAINT_NAME = 'market_update_runs_market_colonia_id_foreign'
            "))->isNotEmpty();

            if ($fk) {
                DB::statement('ALTER TABLE market_update_runs DROP FOREIGN KEY market_update_runs_market_colonia_id_foreign');
            }
            DB::statement('ALTER TABLE market_update_runs MODIFY market_colonia_id BIGINT UNSIGNED NULL');
            DB::statement('ALTER TABLE market_update_runs ADD CONSTRAINT market_update_runs_market_colonia_id_foreign FOREIGN KEY (market_colonia_id) REFERENCES market_colonias(id) ON DELETE CASCADE');

            // Agregar market_zone_id
            $hasZone = collect(DB::select("SHOW COLUMNS FROM market_update_runs LIKE 'market_zone_id'"))->isNotEmpty();
            if (!$hasZone) {
                DB::statement('ALTER TABLE market_update_runs ADD COLUMN market_zone_id BIGINT UNSIGNED NULL AFTER market_colonia_id');
                DB::statement('ALTER TABLE market_update_runs ADD INDEX mur_zone_idx (market_zone_id)');
            }
        } else {
            // SQLite local
            Schema::table('market_update_runs', function (Blueprint $table) {
                $table->unsignedBigInteger('market_zone_id')->nullable()->after('market_colonia_id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('market_update_runs', function (Blueprint $table) {
            $table->dropColumn('market_zone_id');
        });
    }
};
