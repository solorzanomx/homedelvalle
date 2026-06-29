<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('property_valuations', function (Blueprint $table) {
            // ── Seguridad ──────────────────────────────────────────────────
            $table->boolean('input_has_doorman')->default(false)->after('input_has_storage');
            $table->boolean('input_has_intercom')->default(false)->after('input_has_doorman');
            $table->boolean('input_has_security_cameras')->default(false)->after('input_has_intercom');
            $table->boolean('input_has_alarm')->default(false)->after('input_has_security_cameras');

            // ── Amenidades del edificio ────────────────────────────────────
            $table->boolean('input_has_gym')->default(false)->after('input_has_alarm');
            $table->boolean('input_has_pool')->default(false)->after('input_has_gym');
            $table->boolean('input_has_lobby')->default(false)->after('input_has_pool');

            // ── Infraestructura ────────────────────────────────────────────
            $table->boolean('input_has_natural_gas')->default(false)->after('input_has_lobby');
            $table->boolean('input_has_cistern')->default(false)->after('input_has_natural_gas');

            // ── Entorno y acceso ───────────────────────────────────────────
            // principal, residential, quiet, commercial, dead_end
            $table->string('input_street_type', 30)->nullable()->after('input_has_cistern');
            // city, park, garden, street, interior
            $table->string('input_views', 30)->nullable()->after('input_street_type');

            // ── Estado legal ───────────────────────────────────────────────
            // clear, mortgage, pending_deed, unknown
            $table->string('input_legal_status', 30)->nullable()->after('input_views');

            // ── Datos financieros ──────────────────────────────────────────
            $table->unsignedInteger('input_maintenance_fee')->nullable()->after('input_legal_status');
            $table->unsignedSmallInteger('input_renovation_year')->nullable()->after('input_maintenance_fee');
        });
    }

    public function down(): void
    {
        Schema::table('property_valuations', function (Blueprint $table) {
            $table->dropColumn([
                'input_has_doorman', 'input_has_intercom', 'input_has_security_cameras', 'input_has_alarm',
                'input_has_gym', 'input_has_pool', 'input_has_lobby',
                'input_has_natural_gas', 'input_has_cistern',
                'input_street_type', 'input_views', 'input_legal_status',
                'input_maintenance_fee', 'input_renovation_year',
            ]);
        });
    }
};
