<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Administración de renta (2026-10-04): si el propietario contrató que Home del Valle administre
 * la renta de forma continua (cobro mensual, seguimiento de pagos, renovaciones — ver el Acuerdo de
 * Representación, cláusula "Objeto y representación") en vez de solo la colocación del inquilino.
 * Decide si el trato se cierra solo al llegar a Entrega (sin administración) o si el Portal sigue
 * activo durante toda la vigencia del contrato (con administración).
 */
return new class extends Migration {
    public function up(): void {
        Schema::table('rental_processes', function (Blueprint $table) {
            if (! Schema::hasColumn('rental_processes', 'management_contracted')) {
                $table->boolean('management_contracted')->nullable()->comment('null/false = solo colocación; true = Home del Valle administra la renta de forma continua');
            }
            if (! Schema::hasColumn('rental_processes', 'management_contracted_at')) {
                $table->timestamp('management_contracted_at')->nullable();
            }
        });
    }

    public function down(): void {
        Schema::table('rental_processes', function (Blueprint $table) {
            $cols = array_filter(['management_contracted', 'management_contracted_at'], fn($c) => Schema::hasColumn('rental_processes', $c));
            if ($cols) {
                $table->dropColumn(array_values($cols));
            }
        });
    }
};
