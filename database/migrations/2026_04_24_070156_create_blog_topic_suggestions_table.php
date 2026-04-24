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
        Schema::create('blog_topic_suggestions', function (Blueprint $table) {
            $table->id();
            $table->uuid('session_id')->index();
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('reasoning')->nullable();
            $table->json('suggested_keywords')->nullable();
            $table->unsignedTinyInteger('relevance_score')->default(50);
            $table->string('status', 20)->default('pending'); // pending/selected/rejected/converted
            $table->foreignId('converted_post_id')->nullable()->constrained('posts')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blog_topic_suggestions');
    }
};
