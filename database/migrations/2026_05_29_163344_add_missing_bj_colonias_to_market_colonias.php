<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Agrega las 30 colonias oficiales de Benito Juárez que faltaban en el sistema.
 * Fuente: SEPOMEX / buscacp.com (datos 2025).
 *
 * Zonas existentes:
 *   1 → Narvarte & Vértiz
 *   2 → Del Valle
 *   3 → Portales
 *   4 → Álamos & Xoco
 *   5 → Roma Sur & Doctores
 *   6 → Nápoles & Florida
 *   7 → Ciudad de los Deportes & San Pedro
 *   8 → Moderna & Letrán Valle
 */
return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        // ── Corrección de nombre informal → nombre oficial SEPOMEX ────────────
        // "Noche Buena" (informal) → "Nochebuena" (oficial CP 03720)
        DB::table('market_colonias')
            ->where('name', 'Noche Buena')
            ->update(['name' => 'Nochebuena', 'slug' => 'nochebuena']);

        // ── Nuevas colonias organizadas por zona ──────────────────────────────
        $colonias = [

            // Zona 1 — Narvarte & Vértiz (CP 03000-03030)
            1 => [
                ['Atenor Salas', '03010'],
            ],

            // Zona 2 — Del Valle (CP 03100-03250)
            2 => [
                ['Acacias',               '03240'],
                ['Actipan',               '03230'],
                ['Insurgentes San Borja', '03100'],
            ],

            // Zona 3 — Portales (CP 03290-03590)
            3 => [
                ['Miravalle', '03580'],
            ],

            // Zona 4 — Álamos & Xoco (CP 03310-03460)
            4 => [
                ['Residencial Emperadores',   '03320'],
                ['General Pedro María Anaya', '03340'],
                ['Postal',                    '03410'],
                ['Miguel Alemán',             '03420'],
                ['Josefa Ortiz de Domínguez', '03430'],
                ['Niños Héroes',              '03440'],
            ],

            // Zona 5 — Roma Sur & Doctores (CP 03600-03670)
            5 => [
                ['Américas Unidas',   '03610'],
                ['Periodista',        '03620'],
                ['Del Lago',          '03640'],
                ['San Simón Ticumac', '03660'],
            ],

            // Zona 6 — Nápoles & Florida (CP 03720-03850)
            6 => [
                ['San Juan',           '03730'],
                ['8 de Agosto',        '03820'],
                ['Ampliación Nápoles', '03840'],
            ],

            // Zona 7 — Ciudad de los Deportes & San Pedro (CP 03700-03940)
            7 => [
                ['Santa María Nonoalco', '03700'],
                ['San José Insurgentes', '03900'],
                ['Mixcoac',              '03910'],
                ['Insurgentes Mixcoac',  '03920'],
                ['Merced Gómez',         '03930'],
            ],

            // Zona 8 — Moderna & Letrán Valle (CP 03500-03570)
            8 => [
                ['Nativitas',      '03500'],
                ['Iztaccihuatl',   '03520'],
                ['Villa de Cortes', '03530'],
                ['Del Carmen',     '03540'],
                ['Zacahuitzco',    '03550'],
                ['Albert',         '03560'],
            ],
        ];

        foreach ($colonias as $zoneId => $list) {
            foreach ($list as [$name, $cp]) {
                $slug = Str::slug($name);

                // Evitar duplicado si ya existe el slug
                if (DB::table('market_colonias')->where('slug', $slug)->exists()) {
                    continue;
                }

                DB::table('market_colonias')->insert([
                    'market_zone_id' => $zoneId,
                    'name'           => $name,
                    'slug'           => $slug,
                    'alcaldia'       => 'Benito Juárez',
                    'cp'             => $cp,
                    'is_published'   => true,
                    'created_at'     => $now,
                    'updated_at'     => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        $names = [
            'Atenor Salas', 'Acacias', 'Actipan', 'Insurgentes San Borja',
            'Miravalle', 'Residencial Emperadores', 'General Pedro María Anaya',
            'Postal', 'Miguel Alemán', 'Josefa Ortiz de Domínguez', 'Niños Héroes',
            'Américas Unidas', 'Periodista', 'Del Lago', 'San Simón Ticumac',
            'San Juan', '8 de Agosto', 'Ampliación Nápoles',
            'Santa María Nonoalco', 'San José Insurgentes', 'Mixcoac',
            'Insurgentes Mixcoac', 'Merced Gómez',
            'Nativitas', 'Iztaccihuatl', 'Villa de Cortes', 'Del Carmen',
            'Zacahuitzco', 'Albert',
        ];

        DB::table('market_colonias')->whereIn('name', $names)->delete();

        // Revertir corrección de nombre
        DB::table('market_colonias')
            ->where('name', 'Nochebuena')
            ->update(['name' => 'Noche Buena', 'slug' => 'noche-buena']);
    }
};
