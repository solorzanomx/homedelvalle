<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('properties', 'youtube_url')) {
        Schema::table('properties', function (Blueprint $table) {
            $table->string('youtube_url')->nullable()->after('description');
        });
        }

        // Rebuild property_photos with proper columns
        Schema::dropIfExists('property_photos');
        Schema::create('property_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->boolean('is_primary')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn('youtube_url');
        });

        Schema::dropIfExists('property_photos');
        Schema::create('property_photos', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
    }
};
