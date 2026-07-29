<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Algunas filas del catastro traen en "subsidio" un decimal largo en vez del
 * flag 0/1 esperado (hasta 40 caracteres) — dato así en la fuente de SEDUVI,
 * no un error de importación. Se ensancha la columna en vez de descartar
 * esas filas.
 */
return new class extends Migration
{
    public function up(): void
    {
        // SQLite no aplica límites de longitud a VARCHAR (columna dinámica),
        // así que el ALTER solo hace falta en MySQL (producción).
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE catastro_predios MODIFY subsidio VARCHAR(50) NULL');
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE catastro_predios MODIFY subsidio VARCHAR(10) NULL');
        }
    }
};
