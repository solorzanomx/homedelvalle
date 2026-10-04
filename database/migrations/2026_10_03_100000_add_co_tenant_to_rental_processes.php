<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Co-arrendatario (2026-10-03): cuando el contrato queda a nombre de DOS personas (ej. una pareja que renta
 * junta), no es un obligado solidario (ese es un respaldo que no renta); es un segundo inquilino con el mismo
 * cuestionario completo. Mismo mecanismo de captura que obligado_client_id: un Client aparte, sin Portal propio,
 * cuyos datos y documentos los captura el inquilino titular desde su Portal (?para=co_tenant).
 */
return new class extends Migration {
    public function up(): void {
        Schema::table('rental_processes', function (Blueprint $table) {
            if (! Schema::hasColumn('rental_processes', 'co_tenant_client_id')) {
                $table->unsignedBigInteger('co_tenant_client_id')->nullable();
                $table->index('co_tenant_client_id', 'rp_co_tenant_idx'); // nombre corto: MySQL limita a 64 caracteres
            }
        });
    }

    public function down(): void {
        Schema::table('rental_processes', function (Blueprint $table) {
            if (Schema::hasColumn('rental_processes', 'co_tenant_client_id')) {
                $table->dropIndex('rp_co_tenant_idx');
                $table->dropColumn('co_tenant_client_id');
            }
        });
    }
};
