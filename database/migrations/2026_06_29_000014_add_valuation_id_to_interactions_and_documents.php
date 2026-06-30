<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Interactions: vincular a una valuación específica
        Schema::table('interactions', function (Blueprint $table) {
            if (! Schema::hasColumn('interactions', 'valuation_id')) {
                $table->foreignId('valuation_id')
                      ->nullable()
                      ->after('property_id')
                      ->constrained('property_valuations')
                      ->nullOnDelete();
            }
        });

        // Documents: vincular a una valuación + nueva categoría opinion_valor
        Schema::table('documents', function (Blueprint $table) {
            if (! Schema::hasColumn('documents', 'valuation_id')) {
                $table->foreignId('valuation_id')
                      ->nullable()
                      ->after('captacion_id')
                      ->constrained('property_valuations')
                      ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('interactions', function (Blueprint $table) {
            $table->dropForeign(['valuation_id']);
            $table->dropColumn('valuation_id');
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['valuation_id']);
            $table->dropColumn('valuation_id');
        });
    }
};
