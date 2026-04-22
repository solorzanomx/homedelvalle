<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('market_zones', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('short_description')->nullable();
            $table->text('long_description')->nullable();
            $table->decimal('lat_center', 10, 7)->nullable();
            $table->decimal('lng_center', 10, 7)->nullable();
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('market_zones');
    }
};
