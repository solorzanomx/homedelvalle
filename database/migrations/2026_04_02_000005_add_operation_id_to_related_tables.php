<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('operation_id')->nullable()->after('rental_process_id')->constrained()->nullOnDelete();
        });
        Schema::table('documents', function (Blueprint $table) {
            $table->foreignId('operation_id')->nullable()->after('rental_process_id')->constrained()->nullOnDelete();
        });
        Schema::table('contracts', function (Blueprint $table) {
            $table->foreignId('operation_id')->nullable()->after('rental_process_id')->constrained()->nullOnDelete();
        });
        Schema::table('poliza_juridicas', function (Blueprint $table) {
            $table->foreignId('operation_id')->nullable()->after('rental_process_id')->constrained()->nullOnDelete();
        });
        Schema::table('commissions', function (Blueprint $table) {
            $table->foreignId('operation_id')->nullable()->after('deal_id')->constrained()->nullOnDelete();
        });
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
