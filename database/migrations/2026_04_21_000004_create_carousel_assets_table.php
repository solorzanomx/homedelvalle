<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carousel_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carousel_post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('slide_id')->nullable()->constrained('carousel_slides')->nullOnDelete();
            $table->string('type', 20)->default('image');          // image|video|logo
            $table->string('source', 20)->default('upload');       // upload|property|generated
            $table->string('path');
            $table->string('mime_type', 80)->nullable();
            $table->unsignedSmallInteger('width')->nullable();
            $table->unsignedSmallInteger('height')->nullable();
            $table->string('alt_text', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carousel_assets');
    }
};
