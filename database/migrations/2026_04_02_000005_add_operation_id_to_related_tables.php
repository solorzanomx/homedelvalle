<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('tasks', 'operation_id')) {
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('operation_id')->nullable()->after('rental_process_id')->constrained()->nullOnDelete();
        });
        }
        if (!Schema::hasColumn('documents', 'operation_id')) {
        Schema::table('documents', function (Blueprint $table) {
            $table->foreignId('operation_id')->nullable()->after('rental_process_id')->constrained()->nullOnDelete();
        });
        }
        if (!Schema::hasColumn('contracts', 'operation_id')) {
        Schema::table('contracts', function (Blueprint $table) {
            $table->foreignId('operation_id')->nullable()->after('rental_process_id')->constrained()->nullOnDelete();
        });
        }
        if (!Schema::hasColumn('poliza_juridicas', 'operation_id')) {
        Schema::table('poliza_juridicas', function (Blueprint $table) {
            $table->foreignId('operation_id')->nullable()->after('rental_process_id')->constrained()->nullOnDelete();
        });
        }
        if (!Schema::hasColumn('commissions', 'operation_id')) {
        Schema::table('commissions', function (Blueprint $table) {
            $table->foreignId('operation_id')->nullable()->after('deal_id')->constrained()->nullOnDelete();
        });
        }
    }

    public function down(): void
    {
        foreach (['tasks', 'documents', 'contracts', 'poliza_juridicas', 'commissions'] as $tbl) {
            Schema::table($tbl, function (Blueprint $table) {
                $table->dropConstrainedForeignId('operation_id');
            });
        }
    }
};
