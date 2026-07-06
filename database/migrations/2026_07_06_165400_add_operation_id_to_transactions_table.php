<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Puente Finanzas <-> Operation. Commission ya tenía operation_id
 * (2026_04_02_000005_add_operation_id_to_related_tables.php) pero
 * Transaction no — nada escribía en ninguno de los dos hasta este cambio
 * (auditoría 2026-07-06: el dashboard de Finanzas leía Transaction/
 * Commission/Deal, un subsistema paralelo que nadie alimentaba desde
 * Operation).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('transactions', 'operation_id')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->foreignId('operation_id')->nullable()->after('deal_id')->constrained()->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('transactions', 'operation_id')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropConstrainedForeignId('operation_id');
            });
        }
    }
};
