<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carousel_publications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carousel_post_id')->constrained()->cascadeOnDelete();
            $table->string('channel', 30)->default('instagram');   // instagram|facebook|tiktok
            $table->string('status', 20)->default('pending');      // pending|sending|sent|published|failed
            $table->json('payload')->nullable();                   // what was sent to n8n
            $table->string('webhook_url')->nullable();
            $table->json('webhook_response')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carousel_publications');
    }
};
