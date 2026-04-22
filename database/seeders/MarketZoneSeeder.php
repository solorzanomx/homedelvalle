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
        $period = now()->startOfMonth()->toDateString(); // 2026-04-01

        // ── Zonas ─────────────────────────────────────────────────────────
        $zones = [
            [
                'slug'              => 'narvarte-piedad',
                'name'              => 'Narvarte & Piedad',
                'short_description' => 'Zona premium consolidada, alta demanda de profesionistas',
                'sort_order'        => 1,
                'colonias'          => [
                    ['name' => 'Narvarte Poniente', 'slug' => 'narvarte-poniente', 'cp' => '03020'],
                    ['name' => 'Narvarte Oriente',  'slug' => 'narvarte-oriente',  'cp' => '03023'],
                    ['name' => 'Piedad Narvarte',   'slug' => 'piedad-narvarte',   'cp' => '03000'],
                ],
                // Precio m² departamento: [new_low, new_avg, new_high, mid_low, mid_avg, mid_high, old_low, old_avg, old_high]
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
                'colonias'          => [
                    ['name' => 'Del Valle Norte',  'slug' => 'del-valle-norte',  'cp' => '03100'],
                    ['name' => 'Del Valle Centro', 'slug' => 'del-valle-centro', 'cp' => '03100'],
                    ['name' => 'Del Valle Sur',    'slug' => 'del-valle-sur',    'cp' => '03104'],
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
                'colonias'          => [
                    ['name' => 'Portales Norte',   'slug' => 'portales-norte',   'cp' => '03300'],
                    ['name' => 'Portales Sur',     'slug' => 'portales-sur',     'cp' => '03300'],
                    ['name' => 'Portales Oriente', 'slug' => 'portales-oriente', 'cp' => '03570'],
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
                'colonias'          => [
                    ['name' => 'Álamos',     'slug' => 'alamos',     'cp' => '03400'],
                    ['name' => 'Xoco',       'slug' => 'xoco',       'cp' => '03330'],
                    ['name' => 'Niño Jesús', 'slug' => 'nino-jesus', 'cp' => '03820'],
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
                'colonias'          => [
                    ['name' => 'Roma Sur',     'slug' => 'roma-sur',     'cp' => '06760'],
                    ['name' => 'Doctores',     'slug' => 'doctores',     'cp' => '06720'],
                    ['name' => 'Obrera',       'slug' => 'obrera',       'cp' => '06800'],
                    ['name' => 'Buenos Aires', 'slug' => 'buenos-aires', 'cp' => '06780'],
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
                        'is_published'   => true,
                    ]
                );

                // Snapshots por tipo e categoría de antigüedad
                foreach (['new', 'mid', 'old'] as $ageCategory) {
                    // Departamentos
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

                    // Casas
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

        $this->command->info('✓ Zonas: 5 | Colonias: ' . MarketColonia::count() . ' | Snapshots: ' . MarketPriceSnapshot::count());
    }
}
