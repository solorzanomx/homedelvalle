<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Resincroniza el artículo del obligado solidario (arrendador anterior). */
return new class extends Migration
{
    public function up(): void
    {
        $file = database_path('seeders/help-articles/obligado-solidario.md');
        if (! file_exists($file)) {
            return;
        }
        DB::table('help_articles')->where('slug', 'obligado-solidario')->update([
            'content' => preg_replace('/^# .+\n+/', '', file_get_contents($file), 1),
            'updated_at' => now(),
        ]);
    }

    public function down(): void {}
};
