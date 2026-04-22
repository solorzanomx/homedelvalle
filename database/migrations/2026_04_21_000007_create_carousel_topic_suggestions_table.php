<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carousel_topic_suggestions', function (Blueprint $table) {
            $table->id();
            $table->uuid('session_id')->index();
            $table->enum('source', ['web', 'blog', 'manual'])->default('web');
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('reasoning')->nullable();
            $table->string('suggested_type')->default('educational');
            $table->json('suggested_keywords')->nullable();
            $table->unsignedTinyInteger('relevance_score')->default(50);
            $table->unsignedSmallInteger('priority')->default(0);
            $table->enum('status', ['pending', 'selected', 'rejected', 'converted'])->default('pending');
            $table->foreignId('converted_carousel_id')
                  ->nullable()
                  ->constrained('carousel_posts')
                  ->nullOnDelete();
            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carousel_topic_suggestions');
    }
};
