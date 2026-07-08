<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * El artículo "¿Vale más vender tu casa como terreno?" (Post publicado, su
 * contenido vive solo en la BD de producción — local no tiene posts) tenía
 * sus CTAs apuntando a la opinión de valor genérica. Ahora existe la landing
 * dedicada /vende-a-desarrolladora, que es el destino natural de ese lector.
 * Defensiva: no-op si el post no existe (entorno local/fresh).
 */
return new class extends Migration
{
    private const SLUG = 'como-calcular-el-valor-real-de-tu-propiedad-en-benito-juarez-como-terreno-para-desarrollar';

    public function up(): void
    {
        if (! Schema::hasTable('posts')) {
            return;
        }

        $post = DB::table('posts')->where('slug', self::SLUG)->first();
        if (! $post) {
            return;
        }

        DB::table('posts')->where('id', $post->id)->update([
            'ctas' => json_encode([
                [
                    'title'       => '¿Quieres saber cuánto vale tu propiedad como terreno?',
                    'description' => 'Tenemos cartera propia de constructoras buscando predios en Benito Juárez ahora mismo. Evaluación gratuita, confidencial y sin compromiso.',
                    'button_text' => 'Evaluar mi propiedad como terreno',
                    'link'        => '/vende-a-desarrolladora',
                ],
                [
                    'title'       => 'Operamos desde la demanda, no desde la oferta',
                    'description' => 'No salimos a buscar comprador para tu casa: ya sabemos qué constructoras la quieren como terreno. Descúbrelo sin compromiso.',
                    'button_text' => 'Descubrir la demanda por mi predio',
                    'link'        => '/vende-a-desarrolladora',
                ],
            ], JSON_UNESCAPED_UNICODE),
        ]);
    }

    public function down(): void
    {
        // Los CTAs anteriores eran datos editoriales configurados a mano (no
        // hay copia en el repo) — no se pueden restaurar automáticamente; se
        // reeditan desde el admin del blog si hiciera falta.
    }
};
