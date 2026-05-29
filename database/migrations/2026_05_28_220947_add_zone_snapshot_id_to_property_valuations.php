<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('property_valuations', function (Blueprint $table) {
            // Referencia al nuevo snapshot de zona (nueva arquitectura)
            $table->unsignedBigInteger('zone_snapshot_id')->nullable()->after('snapshot_id');
            // Fuente del precio base para debugging
            $table->string('snapshot_source', 30)->nullable()->default('zone')->after('zone_snapshot_id');
        });
    }

    public function down(): void
    {
        Schema::table('property_valuations', function (Blueprint $table) {
            $table->dropColumn(['zone_snapshot_id', 'snapshot_source']);
        });
    }
};
