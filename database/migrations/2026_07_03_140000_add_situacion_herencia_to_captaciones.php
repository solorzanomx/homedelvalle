<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('captaciones', function (Blueprint $table) {
            $table->enum('situacion_herencia', ['no_aplica', 'con_testamento', 'intestado'])
                ->default('no_aplica')
                ->after('urgencia');
        });
    }

    public function down(): void
    {
        Schema::table('captaciones', function (Blueprint $table) {
            $table->dropColumn('situacion_herencia');
        });
    }
};
