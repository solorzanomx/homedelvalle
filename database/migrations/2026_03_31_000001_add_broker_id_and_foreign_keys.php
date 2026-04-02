<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('properties', 'broker_id')) {
        Schema::table('properties', function (Blueprint $table) {
            $table->unsignedBigInteger('broker_id')->nullable()->after('status');
            $table->foreign('broker_id')->references('id')->on('brokers')->nullOnDelete();
        });
        }

        Schema::table('clients', function (Blueprint $table) {
            $table->foreign('broker_id')->references('id')->on('brokers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropForeign(['broker_id']);
            $table->dropColumn('broker_id');
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->dropForeign(['broker_id']);
        });
    }
};
