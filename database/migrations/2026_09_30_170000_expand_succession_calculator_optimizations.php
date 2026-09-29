<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Optimización post-lanzamiento (a pedido de Alejandro): la calculadora "queda disponible para
 * otros posts" (Fase 4) — se activa también en hermano-no-quiere-vender-propiedad-heredada-
 * opciones-legales-cdmx (2° post con más tráfico del cluster herencias): un coheredero en
 * desacuerdo casi siempre implica una sucesión sin resolver, así que el costo/tiempo de esa
 * sucesión es información directamente relevante ahí. NO se activa en isr-venta-propiedad-heredada
 * -mexico-2026 — ese post trata el ISR de la VENTA, no el costo de la sucesión; forzar la misma
 * calculadora ahí sería una herramienta que no responde la pregunta del artículo.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('posts')
            ->where('slug', 'hermano-no-quiere-vender-propiedad-heredada-opciones-legales-cdmx')
            ->update(['show_succession_calculator' => true]);
    }

    public function down(): void
    {
        DB::table('posts')
            ->where('slug', 'hermano-no-quiere-vender-propiedad-heredada-opciones-legales-cdmx')
            ->update(['show_succession_calculator' => false]);
    }
};
