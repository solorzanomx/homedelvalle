<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Vínculo opcional a una empresa/contacto proveedor del catálogo nuevo —
 * adicional a insurance_company/policy_number (texto libre), que se
 * mantienen para no romper pólizas ya existentes. Si se elige una empresa
 * del catálogo, el texto libre puede quedar vacío o servir de respaldo.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('poliza_juridicas', function (Blueprint $table) {
            $table->foreignId('provider_company_id')->nullable()->after('operation_id')->constrained()->nullOnDelete();
            $table->foreignId('provider_contact_id')->nullable()->after('provider_company_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('poliza_juridicas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('provider_company_id');
            $table->dropConstrainedForeignId('provider_contact_id');
        });
    }
};
