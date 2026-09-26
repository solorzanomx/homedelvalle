<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Obligado solidario: se pide cuando la garantía es póliza (sin aval en CDMX). Es un Client con su propio Portal. */
return new class extends Migration {
    public function up(): void {
        Schema::table('rental_processes', function (Blueprint $table) {
            if (! Schema::hasColumn('rental_processes', 'obligado_client_id')) {
                $table->unsignedBigInteger('obligado_client_id')->nullable();
                $table->index('obligado_client_id', 'rp_obligado_idx'); // nombre corto: MySQL limita a 64 caracteres
            }
            if (! Schema::hasColumn('rental_processes', 'obligado_required')) {
                $table->boolean('obligado_required')->nullable()->comment('null = lo define la ruta (póliza ⇒ sí); false = el asesor lo exentó en este trato');
            }
            if (! Schema::hasColumn('rental_processes', 'obligado_invited_at')) {
                $table->timestamp('obligado_invited_at')->nullable();
            }
        });
    }

    public function down(): void {
        Schema::table('rental_processes', function (Blueprint $table) {
            if (Schema::hasColumn('rental_processes', 'obligado_client_id')) {
                $table->dropIndex('rp_obligado_idx');
            }
            $cols = array_filter(['obligado_client_id', 'obligado_required', 'obligado_invited_at'], fn($c) => Schema::hasColumn('rental_processes', $c));
            if ($cols) {
                $table->dropColumn(array_values($cols));
            }
        });
    }
};
