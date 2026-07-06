<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ahora una Commission puede nacer de una Operation (OperationObserver)
 * sin ningun Deal asociado — deal_id ya no puede ser NOT NULL.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commissions', function (Blueprint $table) {
            $table->dropForeign(['deal_id']);
        });
        Schema::table('commissions', function (Blueprint $table) {
            $table->foreignId('deal_id')->nullable()->change();
        });
        Schema::table('commissions', function (Blueprint $table) {
            $table->foreign('deal_id')->references('id')->on('deals')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('commissions', function (Blueprint $table) {
            $table->dropForeign(['deal_id']);
        });
        Schema::table('commissions', function (Blueprint $table) {
            $table->foreignId('deal_id')->nullable(false)->change();
        });
        Schema::table('commissions', function (Blueprint $table) {
            $table->foreign('deal_id')->references('id')->on('deals')->cascadeOnDelete();
        });
    }
};
