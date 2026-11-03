<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Hallazgo 2026-11-03 (serie "PAGARÉS NIÑOS HÉROES 2026-2026", Renta #11): la ruta de garantía
 * 'aval'/'aval_pagares' (TenantRoadmap::ROUTE_AVAL) no tenía NINGÚN lugar para registrar quién es
 * el aval — es un concepto distinto del "obligado solidario" (ObligadoSolidarioService, que por
 * diseño solo aplica a la ruta de póliza, sin aval). El pagaré real trae una tercera firma
 * "AVAL: Cecilia García Padilla Higuera" que el sistema no sabía de dónde sacar.
 *
 * Se modela como texto libre (no como Client con Portal/expediente propio, a diferencia de
 * co-arrendatario/obligado): en los documentos reales el aval solo aparece con su nombre, sin
 * domicilio ni documentos capturados por el sistema — lo captura el asesor directo en el CRM.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rental_processes', function (Blueprint $table) {
            if (!Schema::hasColumn('rental_processes', 'aval_nombre')) {
                $table->string('aval_nombre')->nullable()->after('guarantee_type')
                    ->comment('Nombre del aval (persona), solo para la ruta de garantía aval/aval_pagares. No es el obligado solidario.');
            }
        });
    }

    public function down(): void
    {
        Schema::table('rental_processes', function (Blueprint $table) {
            $table->dropColumn('aval_nombre');
        });
    }
};
