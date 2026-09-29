<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Qué posts muestran la calculadora (Fase 4 del prompt de leads del blog). Empieza en los 2 que pide
 * el prompt; "queda disponible para otros posts" = casilla editable en cualquier post desde el admin.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('posts', 'show_succession_calculator')) {
            Schema::table('posts', fn(Blueprint $t) => $t->boolean('show_succession_calculator')->default(false)->after('cluster'));
        }

        DB::table('posts')
            ->whereIn('slug', ['cuanto-cuesta-sucesion-cdmx-2026', 'propiedad-sin-testamento-cdmx-como-regularizar-vender-2026'])
            ->update(['show_succession_calculator' => true]);
    }

    public function down(): void
    {
        Schema::table('posts', fn(Blueprint $t) => $t->dropColumn('show_succession_calculator'));
    }
};
