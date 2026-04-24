<?php

namespace Database\Seeders;

use App\Models\MarketColonia;
use App\Models\MarketPriceSnapshot;
use App\Models\MarketZone;
use Illuminate\Database\Seeder;

class MarketZoneSeeder extends Seeder
{
    public function run(): void
    {
        $period = now()->startOfMonth()->toDateString();

        // ── Zonas y colonias de Benito Juárez ────────────────────────────────
        // is_published = true  → existentes con datos
        // is_published = false → nuevas, activar desde el admin cuando tengan precios reales
        $zones = [
            [
                'slug'              => 'narvarte-piedad',
                'name'              => 'Narvarte & Vértiz',
                'short_description' => 'Zona premium consolidada, alta demanda de profesionistas',
                'sort_order'        => 1,
                'colonias' => [
                    ['name' => 'Narvarte Poniente',  'slug' => 'narvarte-poniente',  'cp' => '03020', 'published' => true],
                    ['name' => 'Narvarte Oriente',   'slug' => 'narvarte-oriente',   'cp' => '03023', 'published' => true],
                    ['name' => 'Piedad Narvarte',    'slug' => 'piedad-narvarte',    'cp' => '03000', 'published' => true],
                    ['name' => 'Vértiz Narvarte',    'slug' => 'vertiz-narvarte',    'cp' => '03600', 'published' => false],
                ],
                'apt_prices' => [
                    'new' => [82000, 90000, 98000],
                    'mid' => [62000, 70000, 78000],
                    'old' => [48000, 55000, 63000],
                ],
                'house_prices' => [
                    'new' => [75000, 85000, 95000],
                    'mid' => [58000, 65000, 73000],
                    'old' => [44000, 50000, 58000],
                ],
            ],
            [
                'slug'              => 'del-valle',
                'name'              => 'Del Valle',
                'short_description' => 'Una de las zonas más cotizadas de BJ, oferta limitada',
                'sort_order'        => 2,
                'colonias' => [
                    ['name' => 'Del Valle Norte',          'slug' => 'del-valle-norte',          'cp' => '03100', 'published' => true],
                    ['name' => 'Del Valle Centro',         'slug' => 'del-valle-centro',         'cp' => '03100', 'published' => true],
                    ['name' => 'Del Valle Sur',            'slug' => 'del-valle-sur',            'cp' => '03104', 'published' => true],
                    ['name' => 'Tlacoquemécatl del Valle', 'slug' => 'tlacoquemecatl-del-valle', 'cp' => '03200', 'published' => false],
                    ['name' => 'Adolfo López Mateos',      'slug' => 'adolfo-lopez-mateos',      'cp' => '03650', 'published' => false],
                ],
                'apt_prices' => [
                    'new' => [88000, 97000, 106000],
                    'mid' => [68000, 76000, 85000],
                    'old' => [52000, 60000, 68000],
                ],
                'house_prices' => [
                    'new' => [80000, 90000, 102000],
                    'mid' => [62000, 70000, 80000],
                    'old' => [48000, 56000, 64000],
                ],
            ],
            [
                'slug'              => 'portales',
                'name'              => 'Portales',
                'short_description' => 'Zona familiar consolidada, excelente relación precio/m²',
                'sort_order'        => 3,
                'colonias' => [
                    ['name' => 'Portales Norte',   'slug' => 'portales-norte',   'cp' => '03300', 'published' => true],
                    ['name' => 'Portales Sur',     'slug' => 'portales-sur',     'cp' => '03300', 'published' => true],
                    ['name' => 'Portales Oriente', 'slug' => 'portales-oriente', 'cp' => '03570', 'published' => true],
                    ['name' => 'Ermita',           'slug' => 'ermita',           'cp' => '03560', 'published' => false],
                    ['name' => 'Parque San Andrés','slug' => 'parque-san-andres','cp' => '03040', 'published' => false],
                    ['name' => 'Rosedal',          'slug' => 'rosedal',          'cp' => '03100', 'published' => false],
                ],
                'apt_prices' => [
                    'new' => [62000, 70000, 78000],
                    'mid' => [50000, 57000, 64000],
                    'old' => [40000, 47000, 54000],
                ],
                'house_prices' => [
                    'new' => [55000, 63000, 72000],
                    'mid' => [44000, 51000, 58000],
                    'old' => [35000, 42000, 49000],
                ],
            ],
            [
                'slug'              => 'alamos-xoco',
                'name'              => 'Álamos & Xoco',
                'short_description' => 'Zona emergente con creciente demanda de nuevos desarrollos',
                'sort_order'        => 4,
                'colonias' => [
                    ['name' => 'Álamos',           'slug' => 'alamos',           'cp' => '03400', 'published' => true],
                    ['name' => 'Xoco',             'slug' => 'xoco',             'cp' => '03330', 'published' => true],
                    ['name' => 'Niño Jesús',       'slug' => 'nino-jesus',       'cp' => '03820', 'published' => true],
                    ['name' => 'Santa Cruz Atoyac','slug' => 'santa-cruz-atoyac','cp' => '03310', 'published' => false],
                    ['name' => 'General Anaya',    'slug' => 'general-anaya',    'cp' => '03340', 'published' => false],
                ],
                'apt_prices' => [
                    'new' => [58000, 66000, 74000],
                    'mid' => [46000, 53000, 60000],
                    'old' => [36000, 43000, 50000],
                ],
                'house_prices' => [
                    'new' => [52000, 60000, 68000],
                    'mid' => [40000, 47000, 54000],
                    'old' => [32000, 39000, 46000],
                ],
            ],
            [
                'slug'              => 'roma-sur-doctores',
                'name'              => 'Roma Sur & Doctores',
                'short_description' => 'Zona en transición, alta demanda de uso mixto y renta',
                'sort_order'        => 5,
                'colonias' => [
                    ['name' => 'Roma Sur',      'slug' => 'roma-sur',      'cp' => '06760', 'published' => true],
                    ['name' => 'Doctores',      'slug' => 'doctores',      'cp' => '06720', 'published' => true],
                    ['name' => 'Obrera',        'slug' => 'obrera',        'cp' => '06800', 'published' => true],
                    ['name' => 'Buenos Aires',  'slug' => 'buenos-aires',  'cp' => '06780', 'published' => true],
                    ['name' => 'Asturias',      'slug' => 'asturias',      'cp' => '06850', 'published' => false],
                    ['name' => 'Independencia', 'slug' => 'independencia', 'cp' => '03630', 'published' => false],
                ],
                'apt_prices' => [
                    'new' => [68000, 76000, 85000],
                    'mid' => [53000, 61000, 69000],
                    'old' => [42000, 50000, 58000],
                ],
                'house_prices' => [
                    'new' => [60000, 68000, 78000],
                    'mid' => [46000, 54000, 62000],
                    'old' => [36000, 44000, 52000],
                ],
            ],
            [
                'slug'              => 'napoles-florida',
                'name'              => 'Nápoles & Florida',
                'short_description' => 'Corredor Insurgentes, alta plusvalía y desarrollo vertical',
                'sort_order'        => 6,
                'colonias' => [
                    ['name' => 'Nápoles',                 'slug' => 'napoles',                  'cp' => '03810', 'published' => false],
                    ['name' => 'Florida',                  'slug' => 'florida',                  'cp' => '01030', 'published' => false],
                    ['name' => 'Noche Buena',              'slug' => 'noche-buena',              'cp' => '03720', 'published' => false],
                    ['name' => 'Extremadura Insurgentes',  'slug' => 'extremadura-insurgentes',  'cp' => '03740', 'published' => false],
                ],
                'apt_prices' => [
                    'new' => [80000, 89000, 98000],
                    'mid' => [60000, 68000, 77000],
                    'old' => [46000, 54000, 62000],
                ],
                'house_prices' => [
                    'new' => [72000, 82000, 92000],
                    'mid' => [55000, 63000, 71000],
                    'old' => [42000, 49000, 56000],
                ],
            ],
            [
                'slug'              => 'ciudad-deportes-san-pedro',
                'name'              => 'Ciudad de los Deportes & San Pedro',
                'short_description' => 'Zona consolidada al poniente de BJ, tranquila y familiar',
                'sort_order'        => 7,
                'colonias' => [
                    ['name' => 'Ciudad de los Deportes', 'slug' => 'ciudad-de-los-deportes', 'cp' => '03710', 'published' => false],
                    ['name' => 'San Pedro de los Pinos', 'slug' => 'san-pedro-de-los-pinos', 'cp' => '03800', 'published' => false],
                    ['name' => 'Crédito Constructor',    'slug' => 'credito-constructor',    'cp' => '03940', 'published' => false],
                    ['name' => 'Nonoalco',               'slug' => 'nonoalco',               'cp' => '03700', 'published' => false],
                ],
                'apt_prices' => [
                    'new' => [60000, 68000, 76000],
                    'mid' => [48000, 55000, 62000],
                    'old' => [37000, 44000, 51000],
                ],
                'house_prices' => [
                    'new' => [54000, 62000, 70000],
                    'mid' => [42000, 49000, 56000],
                    'old' => [33000, 40000, 47000],
                ],
            ],
            [
                'slug'              => 'moderna-letran',
                'name'              => 'Moderna & Letrán Valle',
                'short_description' => 'Sur oriente de BJ, zona mixta en proceso de densificación',
                'sort_order'        => 8,
                'colonias' => [
                    ['name' => 'Moderna',                  'slug' => 'moderna',                  'cp' => '03510', 'published' => false],
                    ['name' => 'Letrán Valle',             'slug' => 'letran-valle',             'cp' => '03650', 'published' => false],
                    ['name' => 'Amores',                   'slug' => 'amores',                   'cp' => '03960', 'published' => false],
                    ['name' => 'Nueva Diagonal Insurgentes','slug' => 'nueva-diagonal-insurgentes','cp' => '03600', 'published' => false],
                ],
                'apt_prices' => [
                    'new' => [55000, 63000, 71000],
                    'mid' => [44000, 51000, 58000],
                    'old' => [35000, 42000, 49000],
                ],
                'house_prices' => [
                    'new' => [49000, 57000, 65000],
                    'mid' => [39000, 46000, 53000],
                    'old' => [31000, 38000, 45000],
                ],
            ],
        ];

        foreach ($zones as $zoneData) {
            $aptPrices   = $zoneData['apt_prices'];
            $housePrices = $zoneData['house_prices'];

            $zone = MarketZone::updateOrCreate(
                ['slug' => $zoneData['slug']],
                [
                    'name'              => $zoneData['name'],
                    'short_description' => $zoneData['short_description'],
                    'sort_order'        => $zoneData['sort_order'],
                    'is_published'      => true,
                ]
            );

            foreach ($zoneData['colonias'] as $colData) {
                $colonia = MarketColonia::updateOrCreate(
                    ['slug' => $colData['slug']],
                    [
                        'market_zone_id' => $zone->id,
                        'name'           => $colData['name'],
                        'cp'             => $colData['cp'],
                        'alcaldia'       => 'Benito Juárez',
                        'is_published'   => $colData['published'],
                    ]
                );

                foreach (['new', 'mid', 'old'] as $ageCategory) {
                    [$low, $avg, $high] = $aptPrices[$ageCategory];
                    MarketPriceSnapshot::updateOrCreate(
                        [
                            'market_colonia_id' => $colonia->id,
                            'property_type'     => 'apartment',
                            'age_category'      => $ageCategory,
                            'period'            => $period,
                        ],
                        [
                            'price_m2_low'  => $low,
                            'price_m2_avg'  => $avg,
                            'price_m2_high' => $high,
                            'sample_size'   => 0,
                            'confidence'    => 'medium',
                            'source'        => 'manual',
                            'notes'         => 'Datos iniciales de referencia — Benito Juárez Abr 2026',
                        ]
                    );

                    [$low, $avg, $high] = $housePrices[$ageCategory];
                    MarketPriceSnapshot::updateOrCreate(
                        [
                            'market_colonia_id' => $colonia->id,
                            'property_type'     => 'house',
                            'age_category'      => $ageCategory,
                            'period'            => $period,
                        ],
                        [
                            'price_m2_low'  => $low,
                            'price_m2_avg'  => $avg,
                            'price_m2_high' => $high,
                            'sample_size'   => 0,
                            'confidence'    => 'medium',
                            'source'        => 'manual',
                            'notes'         => 'Datos iniciales de referencia — Benito Juárez Abr 2026',
                        ]
                    );
                }
            }
        }

        $total     = MarketColonia::count();
        $published = MarketColonia::where('is_published', true)->count();
        $this->command->info("✓ Zonas: " . MarketZone::count() . " | Colonias: {$total} ({$published} activas) | Snapshots: " . MarketPriceSnapshot::count());
    }
}
