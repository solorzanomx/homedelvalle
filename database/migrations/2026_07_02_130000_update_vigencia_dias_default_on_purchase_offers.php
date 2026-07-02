<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_offers', function (Blueprint $table) {
            $table->unsignedSmallInteger('vigencia_dias')->default(8)->change();
        });
    }

    public function down(): void
    {
        Schema::table('purchase_offers', function (Blueprint $table) {
            $table->unsignedSmallInteger('vigencia_dias')->default(5)->change();
        });
    }
};
