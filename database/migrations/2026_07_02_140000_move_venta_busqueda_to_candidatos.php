<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('operations')
            ->where('type', 'venta')
            ->where('stage', 'busqueda')
            ->update(['stage' => 'candidatos']);
    }

    public function down(): void
    {
        DB::table('operations')
            ->where('type', 'venta')
            ->where('stage', 'candidatos')
            ->update(['stage' => 'busqueda']);
    }
};
