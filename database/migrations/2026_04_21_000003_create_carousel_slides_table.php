<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carousel_slides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carousel_post_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('order')->default(1);
            $table->string('type', 30)->default('cover');          // cover|problem|key_stat|explanation|benefit|example|social_proof|cta
            $table->string('headline', 255)->nullable();
            $table->string('subheadline', 255)->nullable();
            $table->text('body')->nullable();
            $table->string('cta_text', 100)->nullable();
            $table->string('background_image_path')->nullable();
            $table->string('secondary_image_path')->nullable();
            $table->string('overlay_color', 10)->nullable();       // hex
            $table->unsignedTinyInteger('overlay_opacity')->default(60);  // 0-100
            $table->json('custom_data')->nullable();               // template-specific overrides
            $table->string('rendered_image_path')->nullable();     // storage path of PNG
            $table->string('render_status', 20)->default('pending'); // pending|rendering|done|failed
            $table->text('render_error')->nullable();
            $table->boolean('is_locked')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carousel_slides');
    }
};
