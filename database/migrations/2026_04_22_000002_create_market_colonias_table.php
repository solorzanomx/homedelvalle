<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('market_colonias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('market_zone_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('alcaldia')->default('Benito Juárez');
            $table->string('cp', 10)->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamps();

            $table->index('market_zone_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('market_colonias');
    }
};
