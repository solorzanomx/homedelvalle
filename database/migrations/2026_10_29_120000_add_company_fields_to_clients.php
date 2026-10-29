<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Hallazgo 2026-10-29 (caso Andromeda Services / Aída Ornelas, Renta #10): el sistema
 * no distinguía inquilinos persona moral — el checklist de documentos (TenantDocumentRows)
 * le pedía nómina y comprobante de domicilio "a tu nombre" a la representante legal de una
 * empresa, y no pedía nada de lo que sí hace falta (acta constitutiva, poder notarial,
 * constancia de situación fiscal de la empresa). Ver docs/funcionalidades/clientes-persona-moral.md.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            if (!Schema::hasColumn('clients', 'is_company')) {
                $table->boolean('is_company')->default(false)->after('client_type')
                    ->comment('true = el cliente es una persona moral (empresa) representada por el contacto de este registro');
            }
            if (!Schema::hasColumn('clients', 'company_name')) {
                $table->string('company_name')->nullable()->after('is_company')
                    ->comment('Razón social, solo cuando is_company = true. El nombre/RFC/etc. del registro siguen siendo los del representante legal.');
            }
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['is_company', 'company_name']);
        });
    }
};
