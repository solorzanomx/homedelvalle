<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Operation.broker_id es nullable (no todas las Operations tienen broker
 * asignado, auditoria 2026-07-06) — una Commission generada por
 * OperationObserver puede heredar ese null.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commissions', function (Blueprint $table) {
            $table->dropForeign(['broker_id']);
        });
        Schema::table('commissions', function (Blueprint $table) {
            $table->foreignId('broker_id')->nullable()->change();
        });
        Schema::table('commissions', function (Blueprint $table) {
            $table->foreign('broker_id')->references('id')->on('brokers')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('commissions', function (Blueprint $table) {
            $table->dropForeign(['broker_id']);
        });
        Schema::table('commissions', function (Blueprint $table) {
            $table->foreignId('broker_id')->nullable(false)->change();
        });
        Schema::table('commissions', function (Blueprint $table) {
            $table->foreign('broker_id')->references('id')->on('brokers')->cascadeOnDelete();
        });
    }
};
