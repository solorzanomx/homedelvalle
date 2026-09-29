<?php

use App\Models\BlogRedirect;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Fase 6 del prompt de leads del blog: fusión de artículos que compiten entre sí — EN BORRADOR.
 * No despublica ni borra nada: cada pilar sigue exactamente igual en vivo. Esto solo crea un post
 * DRAFT nuevo (status='draft') con el contenido fusionado para que Alejandro lo revise, y dos redirects
 * INACTIVOS por cada post absorbido (apuntando al slug del pilar EN VIVO) listos para activar cuando
 * él apruebe la fusión — nunca se activan solos.
 *
 * Fecha de publicación de cada post involucrado (verificada antes de fusionar, todas > 60 días al
 * 2026-09-30 — ninguna se excluyó por antigüedad):
 *   Grupo 1 (heredada): pilar 2026-04-04, absorbidos 2026-04-12 y 2026-07-21.
 *   Grupo 2 (desarrolladora): pilar 2026-07-12, absorbidos entre 2026-04-24 y 2026-07-30 (el más
 *     nuevo, vender-predio-napoles-desarrolladora-2026, a 62 días — pasa el umbral por poco).
 *   Grupo 3 (H5/H6): pilar 2026-04-10, absorbido 2026-07-20.
 *   Grupo 4 (Narvarte): pilar 2026-07-16, absorbido 2026-07-20.
 *   Grupo 5 (inversión): pilar 2026-04-24, absorbidos entre 2026-07-21 y 2026-07-26.
 *   Grupo 6 (escasez): 2026-07-23 y 2026-07-24.
 */
return new class extends Migration
{
    private function groups(): array
    {
        return [
            [
                'pilar_slug' => 'como-vender-una-propiedad-heredada-en-cdmx-guia-completa-2026',
                'draft_slug' => 'como-vender-una-propiedad-heredada-en-cdmx-guia-completa-2026-borrador-fusion',
                'draft_title' => '[BORRADOR FUSIÓN] Cómo vender una propiedad heredada en CDMX: guía completa',
                'file' => 'fusion-vender-heredada.html',
                'absorbe' => ['vender-propiedad-heredada-benito-juarez-2026', 'heredar-departamento-benito-juarez-pasos-venderlo'],
            ],
            [
                'pilar_slug' => 'vender-casa-constructora-proceso-tiempos-cdmx',
                'draft_slug' => 'vender-casa-constructora-proceso-tiempos-cdmx-borrador-fusion',
                'draft_title' => '[BORRADOR FUSIÓN] Vender tu casa a una constructora: proceso, tiempos y colonias',
                'file' => 'fusion-vender-desarrolladora.html',
                'absorbe' => [
                    'vender-predio-napoles-desarrolladora-2026', 'vender-predio-narvarte-desarrolladora-2026',
                    'vender-predio-portales-constructora-benito-juarez', 'vender-casa-terreno-constructores-benito-juarez-2026',
                    'que-buscan-desarrolladoras-predio-benito-juarez', 'cuanto-pagan-constructoras-terreno-del-valle-2026',
                ],
            ],
            [
                'pilar_slug' => 'propiedades-h5-y-h6-en-benito-juarez-como-identificar-tu-casa-como-potencial-de-desarrollo',
                'draft_slug' => 'propiedades-h5-y-h6-en-benito-juarez-borrador-fusion',
                'draft_title' => '[BORRADOR FUSIÓN] Propiedades H5 y H6 en Benito Juárez',
                'file' => 'fusion-h5-h6.html',
                'absorbe' => ['uso-de-suelo-h6-benito-juarez'],
            ],
            [
                'pilar_slug' => 'vivir-en-narvarte-precios-pros-contras-2026',
                'draft_slug' => 'vivir-en-narvarte-precios-pros-contras-2026-borrador-fusion',
                'draft_title' => '[BORRADOR FUSIÓN] Vivir en Narvarte: precios, pros y contras',
                'file' => 'fusion-narvarte.html',
                'absorbe' => ['narvarte-oriente-vs-narvarte-poniente-2026'],
            ],
            [
                'pilar_slug' => 'invertir-inmuebles-benito-juarez-2026',
                'draft_slug' => 'invertir-inmuebles-benito-juarez-2026-borrador-fusion',
                'draft_title' => '[BORRADOR FUSIÓN] Invertir en inmuebles en Benito Juárez',
                'file' => 'fusion-invertir-bj.html',
                'absorbe' => [
                    'benito-juarez-vs-cuauhtemoc-inversion-inmobiliaria-2026', 'comprar-para-rentar-benito-juarez',
                    'yield-renta-del-valle-narvarte-benito-juarez', 'mercado-renta-benito-juarez-2026',
                ],
            ],
            [
                'pilar_slug' => 'escasez-suelo-benito-juarez-que-significa-tu-predio',
                'draft_slug' => 'escasez-suelo-benito-juarez-borrador-fusion',
                'draft_title' => '[BORRADOR FUSIÓN] Por qué se agota el suelo en Benito Juárez',
                'file' => 'fusion-escasez-suelo.html',
                'absorbe' => ['obra-nueva-benito-juarez-2026-escasez-oferta'],
            ],
        ];
    }

    public function up(): void
    {
        foreach ($this->groups() as $g) {
            $pilar = DB::table('posts')->where('slug', $g['pilar_slug'])->first();
            if (! $pilar) {
                continue;   // el pilar no existe aquí (local no tiene el catálogo real) — no se crea el borrador
            }

            $file = database_path('seeders/blog-posts/' . $g['file']);
            if (! file_exists($file)) {
                continue;
            }

            DB::table('posts')->updateOrInsert(
                ['slug' => $g['draft_slug']],
                [
                    'user_id' => $pilar->user_id,
                    'title' => $g['draft_title'],
                    'body' => file_get_contents($file),
                    'excerpt' => 'Borrador de fusión (Fase 6) — pendiente de revisión. No publicar sin que Alejandro lo apruebe y lo convierta en la nueva versión de ' . $g['pilar_slug'] . '.',
                    'category_id' => $pilar->category_id,
                    'cluster' => $pilar->cluster,
                    'status' => 'draft',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            foreach ($g['absorbe'] as $slug) {
                $absorbed = DB::table('posts')->where('slug', $slug)->first();
                if (! $absorbed) {
                    continue;   // ese post absorbido no existe aquí — no se inventa el redirect
                }

                BlogRedirect::updateOrCreate(
                    ['from_path' => '/blog/' . $slug],
                    ['to_path' => '/blog/' . $g['pilar_slug'], 'status' => 301, 'active' => false, 'notes' => 'Fase 6: fusión en borrador, pendiente de que Alejandro la revise y active']
                );
            }
        }
    }

    public function down(): void
    {
        foreach ($this->groups() as $g) {
            DB::table('posts')->where('slug', $g['draft_slug'])->delete();
            foreach ($g['absorbe'] as $slug) {
                BlogRedirect::where('from_path', '/blog/' . $slug)
                    ->where('notes', 'like', 'Fase 6:%')
                    ->delete();
            }
        }
    }
};
