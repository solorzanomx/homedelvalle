<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_stories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('platform', ['instagram', 'facebook'])->default('instagram');
            $table->enum('media_type', ['image', 'video'])->default('image');
            $table->enum('source_type', ['property', 'blog_post', 'manual'])->default('manual');
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('headline', 100)->nullable();
            $table->text('caption')->nullable();
            $table->json('sticker_hashtags')->nullable();
            $table->string('sticker_location', 100)->nullable();
            $table->string('sticker_link', 255)->nullable();
            $table->string('background_image_path')->nullable();
            $table->string('rendered_image_path')->nullable();
            $table->enum('render_status', ['pending', 'rendering', 'done', 'failed'])->default('pending');
            $table->text('render_error')->nullable();
            $table->enum('status', ['draft', 'scheduled', 'published', 'failed'])->default('draft');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->string('platform_story_id', 100)->nullable();
            $table->string('platform_story_url', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_stories');
    }
};
