<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carousel_posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('type', 30)->default('commercial');    // commercial|educational|capture|informative|branding
            $table->string('source_type', 20)->nullable();        // property|blog_post|free
            $table->unsignedBigInteger('source_id')->nullable();   // FK to source (polymorphic-style, no constraint)
            $table->foreignId('template_id')->nullable()->constrained('carousel_templates')->nullOnDelete();
            $table->string('status', 20)->default('draft');       // draft|generating|review|approved|published|archived
            $table->string('caption_short', 280)->nullable();     // Instagram caption short
            $table->text('caption_long')->nullable();
            $table->json('hashtags')->nullable();
            $table->string('cta', 255)->nullable();
            $table->text('ai_prompt_used')->nullable();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_version_id')->nullable();  // set after carousel_versions exists
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carousel_posts');
    }
};
