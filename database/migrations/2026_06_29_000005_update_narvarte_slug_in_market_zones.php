<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::table('market_zones')
            ->where('slug', 'narvarte-piedad')
            ->update(['slug' => 'narvarte']);
    }
    public function down(): void
    {
        DB::table('market_zones')
            ->where('slug', 'narvarte')
            ->update(['slug' => 'narvarte-piedad']);
    }
};
