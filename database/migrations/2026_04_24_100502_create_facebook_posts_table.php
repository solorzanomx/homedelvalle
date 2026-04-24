<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('facebook_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->enum('source_type', ['blog_post', 'perplexity', 'manual'])->default('manual');
            $table->unsignedBigInteger('source_id')->nullable()->index();
            $table->string('template', 30)->default('fb-dark');
            $table->string('headline')->nullable();
            $table->string('subheadline')->nullable();
            $table->text('body_text')->nullable();
            $table->text('caption')->nullable();
            $table->json('hashtags')->nullable();
            $table->string('background_image_path')->nullable();
            $table->string('rendered_image_path')->nullable();
            $table->enum('render_status', ['pending', 'rendering', 'done', 'failed'])->default('pending');
            $table->text('render_error')->nullable();
            $table->enum('status', ['draft', 'review', 'published'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facebook_posts');
    }
};
