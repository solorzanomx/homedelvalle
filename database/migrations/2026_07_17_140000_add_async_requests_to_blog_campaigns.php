<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Generar mapa (~2 min) y producir borrador (~5 min) exceden el límite de
 * 100s del proxy de Cloudflare (502). Los botones ahora dejan la orden en
 * estas columnas y blog:campaign-work la ejecuta por cron.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_campaigns', function (Blueprint $table) {
            $table->timestamp('map_requested_at')->nullable();
            $table->unsignedSmallInteger('map_requested_count')->nullable();
            $table->timestamp('produce_requested_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('blog_campaigns', function (Blueprint $table) {
            $table->dropColumn(['map_requested_at', 'map_requested_count', 'produce_requested_at']);
        });
    }
};
