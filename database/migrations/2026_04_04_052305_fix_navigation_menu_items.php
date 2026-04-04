<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Remove old/duplicate nav items
        DB::table('pages')->where('slug', 'quienes-somos')->update(['show_in_nav' => false]);
        DB::table('pages')->where('slug', 'office')->update(['show_in_nav' => false]);

        // Fix existing nav items
        DB::table('pages')->where('slug', 'inicio')->update([
            'nav_order' => 0, 'nav_label' => 'Inicio', 'nav_url' => '/', 'nav_style' => 'link',
        ]);
        DB::table('pages')->where('slug', 'propiedades')->update([
            'nav_order' => 1, 'nav_url' => '/propiedades', 'nav_style' => 'link',
        ]);
        DB::table('pages')->where('slug', 'nosotros')->update([
            'nav_order' => 4, 'nav_url' => '/nosotros', 'nav_style' => 'link',
        ]);
        DB::table('pages')->where('slug', 'blog')->update([
            'nav_order' => 5, 'nav_url' => '/blog', 'nav_style' => 'link',
        ]);
        DB::table('pages')->where('slug', 'contacto')->update([
            'nav_order' => 6, 'nav_url' => '/contacto', 'nav_style' => 'link',
        ]);

        // Create Servicios nav entry if it doesn't exist
        if (!DB::table('pages')->where('slug', 'servicios')->exists()) {
            DB::table('pages')->insert([
                'title' => 'Servicios',
                'slug' => 'servicios',
                'body' => '',
                'is_published' => true,
                'show_in_nav' => true,
                'nav_order' => 2,
                'nav_label' => 'Servicios',
                'nav_url' => '/servicios',
                'nav_style' => 'link',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            DB::table('pages')->where('slug', 'servicios')->update([
                'show_in_nav' => true, 'nav_order' => 2, 'nav_label' => 'Servicios',
                'nav_url' => '/servicios', 'nav_style' => 'link',
            ]);
        }

        // Create Vende tu Propiedad nav entry if it doesn't exist
        if (!DB::table('pages')->where('slug', 'vende-tu-propiedad')->exists()) {
            DB::table('pages')->insert([
                'title' => 'Vende tu Propiedad',
                'slug' => 'vende-tu-propiedad',
                'body' => '',
                'is_published' => true,
                'show_in_nav' => true,
                'nav_order' => 3,
                'nav_label' => 'Vende tu Propiedad',
                'nav_url' => '/vende-tu-propiedad',
                'nav_style' => 'link',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            DB::table('pages')->where('slug', 'vende-tu-propiedad')->update([
                'show_in_nav' => true, 'nav_order' => 3, 'nav_label' => 'Vende tu Propiedad',
                'nav_url' => '/vende-tu-propiedad', 'nav_style' => 'link',
            ]);
        }
    }

    public function down(): void
    {
        // Restore original nav state
        DB::table('pages')->where('slug', 'quienes-somos')->update(['show_in_nav' => true]);
        DB::table('pages')->where('slug', 'office')->update(['show_in_nav' => true]);
        DB::table('pages')->whereIn('slug', ['servicios', 'vende-tu-propiedad'])
            ->where('body', '')->delete();
    }
};
