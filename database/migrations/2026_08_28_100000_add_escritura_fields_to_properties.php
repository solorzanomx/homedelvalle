<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Datos de la escritura pública / inscripción registral del inmueble —
 * necesarios para la Declaración de Propiedad del Acuerdo de Representación
 * (Renta), donde el propietario se identifica bajo protesta de decir verdad.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            if (! Schema::hasColumn('properties', 'folio_real')) {
                $table->string('folio_real', 80)->nullable()->after('inmuebles24_ad_code');
            }
            if (! Schema::hasColumn('properties', 'escritura_numero')) {
                $table->string('escritura_numero', 60)->nullable()->after('folio_real');
            }
            if (! Schema::hasColumn('properties', 'escritura_fecha')) {
                $table->date('escritura_fecha')->nullable()->after('escritura_numero');
            }
            if (! Schema::hasColumn('properties', 'notario_nombre')) {
                $table->string('notario_nombre', 150)->nullable()->after('escritura_fecha');
            }
            if (! Schema::hasColumn('properties', 'notario_numero')) {
                $table->string('notario_numero', 20)->nullable()->after('notario_nombre');
            }
            if (! Schema::hasColumn('properties', 'notario_plaza')) {
                $table->string('notario_plaza', 100)->nullable()->after('notario_numero');
            }
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $columns = ['folio_real', 'escritura_numero', 'escritura_fecha', 'notario_nombre', 'notario_numero', 'notario_plaza'];
            $existing = array_filter($columns, fn ($col) => Schema::hasColumn('properties', $col));
            if ($existing) {
                $table->dropColumn(array_values($existing));
            }
        });
    }
};
