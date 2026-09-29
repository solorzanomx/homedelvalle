<?php

use App\Support\BlogCluster;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Fase 3 del prompt de leads del blog: en qué etapa del funnel está el lector. Nullable — cuando es
 * null, BlogCluster::forPost() lo sigue derivando de category+slug (el heurístico de la Fase 2 no
 * desaparece, esto solo permite CORREGIRLO a mano cuando la categoría no basta).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('posts', 'cluster')) {
            Schema::table('posts', fn(Blueprint $t) => $t->string('cluster', 40)->nullable()->after('category_id'));
        }

        // Backfill: solo se escribe donde el heurístico ya deriva algo — deja el resto en null
        // (sigue resolviéndose en caliente) en vez de "adivinar" con un valor por defecto.
        DB::table('posts')->select('id', 'slug', 'category_id')->orderBy('id')->chunk(200, function ($rows) {
            foreach ($rows as $row) {
                $post = \App\Models\Post::find($row->id);
                $cluster = BlogCluster::forPost($post);
                if ($cluster) {
                    DB::table('posts')->where('id', $row->id)->update(['cluster' => $cluster]);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('posts', fn(Blueprint $t) => $t->dropColumn('cluster'));
    }
};
