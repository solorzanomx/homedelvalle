<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fase 1-C: Extiende properties con campos específicos de renta.
 * Nota: properties.furnished (string) ya existe; agregamos campos nuevos.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            if (! Schema::hasColumn('properties', 'allows_pets')) {
                $table->boolean('allows_pets')
                    ->default(false)
                    ->after('furnished')
                    ->comment('¿Acepta mascotas?');
            }
            if (! Schema::hasColumn('properties', 'minimum_lease_months')) {
                $table->unsignedSmallInteger('minimum_lease_months')
                    ->nullable()
                    ->after('allows_pets')
                    ->comment('Duración mínima del contrato en meses');
            }
            if (! Schema::hasColumn('properties', 'included_services')) {
                $table->json('included_services')
                    ->nullable()
                    ->after('minimum_lease_months')
                    ->comment('Servicios incluidos en la renta: agua, gas, internet, etc.');
            }
            if (! Schema::hasColumn('properties', 'available_from')) {
                $table->date('available_from')
                    ->nullable()
                    ->after('included_services')
                    ->comment('Fecha a partir de la cual el inmueble está disponible para renta');
            }
            if (! Schema::hasColumn('properties', 'monthly_rent_price')) {
                $table->unsignedBigInteger('monthly_rent_price')
                    ->nullable()
                    ->after('available_from')
                    ->comment('Precio de renta mensual en centavos (evita decimales)');
            }
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $cols = ['allows_pets', 'minimum_lease_months', 'included_services', 'available_from', 'monthly_rent_price'];
            $existing = array_filter($cols, fn($c) => Schema::hasColumn('properties', $c));
            if ($existing) $table->dropColumn(array_values($existing));
        });
    }
};
