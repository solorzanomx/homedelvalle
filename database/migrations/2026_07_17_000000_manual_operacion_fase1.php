<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Manual de Operación — Fase 0 + Fase 1 (2026-07-17).
 *
 * El centro de ayuda quedó congelado en abril 2026 y el sistema cambió por
 * completo (captación→exclusiva, portal del cliente, expediente, pipeline
 * inquilinos, proveedores, leads de portales…). Esta migración:
 *
 *  1. Reestructura categorías para reflejar la operación real (negocio,
 *     captación, predios, venta, rentas, portal del cliente).
 *  2. Despublica los artículos que quedaron MENTIROSOS (describen el
 *     sistema viejo) — peor que faltar es desinformar.
 *  3. Siembra los 12 artículos de Fase 1 ("el camino del dinero") desde
 *     database/seeders/help-articles/*.md — mismo patrón que blog-posts:
 *     cambios futuros a los .md requieren su propia migración de resync.
 *
 * Idempotente: categorías y artículos por slug (updateOrCreate).
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Categorías: nuevas + renombradas + reordenadas ──
        $categorias = [
            ['slug' => 'negocio',        'name' => 'El negocio y el sistema',        'icon' => '🧭', 'sort_order' => 1],
            ['slug' => 'primeros-pasos', 'name' => 'Primeros pasos',                 'icon' => '🚀', 'sort_order' => 2],
            ['slug' => 'captacion',      'name' => 'Captación de exclusivas',        'icon' => '🔑', 'sort_order' => 3],
            ['slug' => 'predios',        'name' => 'Predios → Desarrolladoras',      'icon' => '🏗️', 'sort_order' => 4],
            ['slug' => 'venta',          'name' => 'Venta: de candidato a escritura', 'icon' => '🏛️', 'sort_order' => 5],
            ['slug' => 'rentas',         'name' => 'Rentas e inquilinos',            'icon' => '📄', 'sort_order' => 6],
            ['slug' => 'clientes-leads', 'name' => 'Leads y clientes',               'icon' => '👤', 'sort_order' => 7],
            ['slug' => 'portal-cliente', 'name' => 'Portal del Cliente',             'icon' => '🔐', 'sort_order' => 8],
            ['slug' => 'propiedades',    'name' => 'Propiedades',                    'icon' => '🏠', 'sort_order' => 9],
            ['slug' => 'operaciones',    'name' => 'Operaciones y Pipeline',         'icon' => '📋', 'sort_order' => 10],
            ['slug' => 'tareas',         'name' => 'Tareas y Seguimiento',           'icon' => '✅', 'sort_order' => 11],
            ['slug' => 'tratos',         'name' => 'Tratos y Negociaciones',         'icon' => '🤝', 'sort_order' => 12],
            ['slug' => 'marketing',      'name' => 'Marketing y Campañas',           'icon' => '📣', 'sort_order' => 13],
            ['slug' => 'automatizaciones','name' => 'Automatizaciones',              'icon' => '⚡', 'sort_order' => 14],
            ['slug' => 'finanzas',       'name' => 'Finanzas',                       'icon' => '💰', 'sort_order' => 15],
            ['slug' => 'cms',            'name' => 'Sitio Web y CMS',                'icon' => '🌐', 'sort_order' => 16],
            ['slug' => 'configuracion',  'name' => 'Configuración',                  'icon' => '⚙️', 'sort_order' => 17],
        ];

        $catIds = [];
        foreach ($categorias as $cat) {
            $existing = DB::table('help_categories')->where('slug', $cat['slug'])->first();
            if ($existing) {
                DB::table('help_categories')->where('id', $existing->id)->update([
                    'name' => $cat['name'], 'icon' => $cat['icon'], 'sort_order' => $cat['sort_order'], 'updated_at' => now(),
                ]);
                $catIds[$cat['slug']] = $existing->id;
            } else {
                $catIds[$cat['slug']] = DB::table('help_categories')->insertGetId(
                    $cat + ['created_at' => now(), 'updated_at' => now()]
                );
            }
        }

        // ── 2. Despublicar artículos que describen el sistema viejo ──
        DB::table('help_articles')->whereIn('slug', [
            'easybroker',            // "Integración EasyBroker" (Propiedades) — city IDs inventados
            'config-easybroker',     // "Integración EasyBroker" (Configuración) — ídem
            'flujo-completo-leads',  // pre vendedor_predio / temperaturas / atribución
            'comisiones',            // pre esquema proporcional / adéndum
            'proceso-renta',         // pre pipeline inquilino / RentalProcess automático
            'contratos',             // pre Acuerdo de Representación / doc registry
        ])->update(['is_published' => false, 'updated_at' => now()]);

        // ── 3. Sembrar los 12 artículos de Fase 1 ──
        $articulos = [
            // slug => [categoría, título, sort]
            'como-funciona-hdv'          => ['negocio',        'Cómo funciona Home del Valle: el negocio detrás del sistema', 1],
            'flujo-captacion-exclusiva'  => ['captacion',      'Captación: del lead de propietario a la exclusiva firmada', 1],
            'documentos-captacion'       => ['captacion',      'Documentos de captación: Opinión de Valor, Presentación y Acuerdo de Representación', 2],
            'funnel-predios'             => ['predios',        'Venta a desarrolladora: el funnel de predios', 1],
            'pipeline-venta'             => ['venta',          'Pipeline de venta: candidatos, oferta y cierre', 1],
            'expediente-vendedor'        => ['venta',          'Expediente del Vendedor: la documentación de notaría', 2],
            'comisiones-venta'           => ['venta',          'Comisiones de venta: esquema y cobro', 3],
            'renta-de-punta-a-punta'     => ['rentas',         'Renta de punta a punta: del lead al proceso de renta', 1],
            'leads-del-sitio'            => ['clientes-leads', 'Leads del sitio: formularios, temperaturas y conversión', 1],
            'leads-easybroker-portales'  => ['clientes-leads', 'Leads de EasyBroker y portales', 2],
            'portal-del-cliente'         => ['portal-cliente', 'El Portal del Cliente: qué ve tu cliente y por qué importa', 1],
            'easybroker-integracion'     => ['propiedades',    'EasyBroker: qué hace la integración hoy', 5],
        ];

        foreach ($articulos as $slug => [$catSlug, $title, $sort]) {
            $file = database_path("seeders/help-articles/{$slug}.md");
            if (! file_exists($file)) {
                continue; // defensiva: no abortar el deploy por un archivo faltante
            }

            $content = file_get_contents($file);
            // El H1 del archivo es el título — el centro de ayuda ya muestra
            // el title como encabezado, quitarlo evita el doble título.
            $content = preg_replace('/^# .+\n+/', '', $content, 1);

            $existing = DB::table('help_articles')->where('slug', $slug)->first();
            $data = [
                'help_category_id' => $catIds[$catSlug],
                'title'            => $title,
                'content'          => $content,
                'sort_order'       => $sort,
                'is_published'     => true,
                'updated_at'       => now(),
            ];

            if ($existing) {
                DB::table('help_articles')->where('id', $existing->id)->update($data);
            } else {
                DB::table('help_articles')->insert($data + ['slug' => $slug, 'view_count' => 0, 'created_at' => now()]);
            }
        }
    }

    public function down(): void
    {
        DB::table('help_articles')->whereIn('slug', [
            'como-funciona-hdv', 'flujo-captacion-exclusiva', 'documentos-captacion',
            'funnel-predios', 'pipeline-venta', 'expediente-vendedor', 'comisiones-venta',
            'renta-de-punta-a-punta', 'leads-del-sitio', 'leads-easybroker-portales',
            'portal-del-cliente', 'easybroker-integracion',
        ])->delete();

        DB::table('help_articles')->whereIn('slug', [
            'easybroker', 'config-easybroker', 'flujo-completo-leads',
            'comisiones', 'proceso-renta', 'contratos',
        ])->update(['is_published' => true]);
    }
};
