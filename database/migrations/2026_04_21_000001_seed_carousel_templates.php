<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Fix existing template to use correct blade_view folder name
        DB::table('carousel_templates')->where('id', 1)->update([
            'blade_view' => 'premium-dark',
            'name'       => 'Premium Oscuro',
        ]);

        $now = now();
        $templates = [
            ['slug' => 'hdv-claro',       'name' => 'HDV Claro',       'blade_view' => 'hdv-claro',       'description' => 'Fondo blanco, texto marino, acento azul. Limpio y profesional.'],
            ['slug' => 'hdv-degradado',   'name' => 'HDV Degradado',   'blade_view' => 'hdv-degradado',   'description' => 'Degradado azul marino a azul cielo. Moderno y energético.'],
            ['slug' => 'hdv-marino',      'name' => 'HDV Marino',      'blade_view' => 'hdv-marino',      'description' => 'Azul marino profundo con tipografía bold. Autoridad y lujo.'],
            ['slug' => 'hdv-editorial',   'name' => 'HDV Editorial',   'blade_view' => 'hdv-editorial',   'description' => 'Blanco y negro editorial. Tipografía grande, estilo revista.'],
            ['slug' => 'hdv-foto-limpia', 'name' => 'HDV Foto Limpia', 'blade_view' => 'hdv-foto-limpia', 'description' => 'Foto de fondo con overlay mínimo (25%). Ideal para inmuebles.'],
        ];

        foreach ($templates as $tpl) {
            DB::table('carousel_templates')->insertOrIgnore(array_merge($tpl, [
                'is_active'  => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }
    }

    public function down(): void
    {
        DB::table('carousel_templates')->whereIn('slug', [
            'hdv-claro', 'hdv-degradado', 'hdv-marino', 'hdv-editorial', 'hdv-foto-limpia',
        ])->delete();

        DB::table('carousel_templates')->where('id', 1)->update([
            'blade_view' => 'plantilla-1-dark',
            'name'       => 'premium 1',
        ]);
    }
};
