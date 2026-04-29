<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Insert only if slug doesn't already exist
        $exists = DB::table('post_categories')
            ->where('slug', 'renta-e-inquilinos')
            ->exists();

        if ($exists) {
            return;
        }

        DB::table('post_categories')->insert([
            'name'        => 'Renta e Inquilinos',
            'slug'        => 'renta-e-inquilinos',
            'description' => 'Guías, consejos y tendencias sobre renta inmobiliaria en Benito Juárez: para propietarios que quieren rentar su inmueble y para quienes buscan dónde vivir.',
            'color'       => '#0ea5e9',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('post_categories')
            ->where('slug', 'renta-e-inquilinos')
            ->delete();
    }
};
