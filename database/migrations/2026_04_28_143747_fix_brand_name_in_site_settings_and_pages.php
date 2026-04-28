<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Fix site_name capitalization: 'Home del valle' → 'Home del Valle'
        DB::table('site_settings')
            ->where('site_name', 'Home del valle')
            ->update(['site_name' => 'Home del Valle']);

        // Fix pages meta_title: remove duplicate brand suffix (both capitalizations)
        DB::table('pages')
            ->where('slug', 'comprar')
            ->whereRaw("meta_title LIKE '%| Home del Valle%' OR meta_title LIKE '%| Home del valle%'")
            ->update(['meta_title' => 'Búsqueda asistida de inmuebles en Benito Juárez']);

        DB::table('pages')
            ->where('slug', 'desarrolladores-e-inversionistas')
            ->whereRaw("meta_title LIKE '%| Home del Valle%' OR meta_title LIKE '%| Home del valle%'")
            ->update(['meta_title' => 'Captación de predios e inversión inmobiliaria en Benito Juárez']);
    }

    public function down(): void
    {
        DB::table('site_settings')
            ->where('site_name', 'Home del Valle')
            ->update(['site_name' => 'Home del valle']);

        DB::table('pages')
            ->where('slug', 'comprar')
            ->update(['meta_title' => 'Búsqueda asistida de inmuebles en Benito Juárez | Home del Valle']);

        DB::table('pages')
            ->where('slug', 'desarrolladores-e-inversionistas')
            ->update(['meta_title' => 'Captación de predios e inversión inmobiliaria en Benito Juárez | Home del Valle']);
    }
};
