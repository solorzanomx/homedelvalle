<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Publica todas las colonias de Benito Juárez (CP 03xxx) que estaban
 * marcadas como is_published = false y por eso no aparecían en ningún selector.
 *
 * Las colonias de otras alcaldías (Cuauhtémoc CP 06xxx, Álvaro Obregón CP 01xxx)
 * se dejan intactas.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Publicar todas las colonias con CP de Benito Juárez (03000–03999)
        DB::table('market_colonias')
            ->where('is_published', false)
            ->where('cp', 'like', '03%')
            ->update(['is_published' => true, 'updated_at' => now()]);
    }

    public function down(): void
    {
        // No hay forma segura de revertir sin saber cuáles estaban unpublished antes
        // Se omite el rollback para evitar despublicar colonias activas
    }
};
