<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * poliza_juridicas.rental_process_id era NOT NULL desde la creación de la
 * tabla, pero PolizaJuridicaController::storeForOperation() (usado para
 * generar una póliza directo desde una Operation de renta, ANTES de que
 * exista un RentalProcess — ver puente Operation->RentalProcess,
 * project_homedelvalle_auditoria_2026_07_04.md) nunca lo pasa. Bug real
 * encontrado al probar el puente 2026-07-04: crear una póliza desde una
 * Operation siempre fallaba con violación NOT NULL. No se usa ->change()
 * (requeriría doctrine/dbal, no instalado) — SQLite no soporta ALTER
 * COLUMN directo, así que se recrea la tabla ahí; MySQL sí soporta MODIFY.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            Schema::create('poliza_juridicas_tmp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('rental_process_id')->nullable()->constrained()->cascadeOnDelete();
                $table->foreignId('tenant_client_id')->nullable()->constrained('clients')->nullOnDelete();
                $table->string('insurance_company')->nullable();
                $table->string('policy_number')->nullable();
                $table->string('status')->default('pending');
                $table->timestamp('submitted_at')->nullable();
                $table->timestamp('review_started_at')->nullable();
                $table->timestamp('resolved_at')->nullable();
                $table->text('rejection_reason')->nullable();
                $table->decimal('cost', 12, 2)->nullable();
                $table->string('currency', 3)->default('MXN');
                $table->date('coverage_start')->nullable();
                $table->date('coverage_end')->nullable();
                $table->text('notes')->nullable();
                $table->foreignId('operation_id')->nullable()->constrained()->nullOnDelete();
                $table->timestamps();
            });

            DB::statement('INSERT INTO poliza_juridicas_tmp SELECT * FROM poliza_juridicas');
            Schema::drop('poliza_juridicas');
            Schema::rename('poliza_juridicas_tmp', 'poliza_juridicas');
        } else {
            DB::statement('ALTER TABLE poliza_juridicas MODIFY rental_process_id BIGINT UNSIGNED NULL');
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE poliza_juridicas MODIFY rental_process_id BIGINT UNSIGNED NOT NULL');
        }
    }
};
