<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        /**
         * Fase 1-F: Audit log del portal.
         * Registra accesos, impersonaciones y acciones sensibles.
         */
        Schema::create('portal_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete()
                ->comment('User que realizó la acción (admin o cliente)');
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete()
                ->comment('Cliente afectado (si aplica)');
            $table->string('action', 80)->comment('login | logout | impersonate_start | impersonate_end | document_download | etc.');
            $table->string('target_type', 80)->nullable()->comment('Modelo afectado: Document, RentalProcess, etc.');
            $table->unsignedBigInteger('target_id')->nullable();
            $table->ipAddress('ip')->nullable();
            $table->string('user_agent', 300)->nullable();
            $table->json('metadata')->nullable()->comment('Datos adicionales según la acción');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['user_id', 'action', 'created_at']);
            $table->index(['client_id', 'created_at']);
            $table->index('action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('portal_audit_logs');
    }
};
