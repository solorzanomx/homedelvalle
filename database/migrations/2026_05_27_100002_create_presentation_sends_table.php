<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('presentation_sends', function (Blueprint $table) {
            $table->id();
            $table->foreignId('captacion_id')->constrained('captaciones')->cascadeOnDelete();
            $table->enum('channel', ['email', 'whatsapp', 'download']);
            $table->foreignId('sent_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('recipient_email')->nullable();
            $table->string('recipient_phone', 30)->nullable();
            $table->string('tracking_token', 64)->unique();
            $table->timestamp('sent_at')->useCurrent();
            $table->timestamp('email_opened_at')->nullable();
            $table->timestamp('link_clicked_at')->nullable();
            $table->timestamp('pdf_viewed_at')->nullable();
            $table->unsignedInteger('pdf_view_count')->default(0);
            $table->timestamp('pdf_downloaded_at')->nullable();
            $table->ipAddress('last_view_ip')->nullable();
            $table->string('last_view_user_agent')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['captacion_id', 'channel']);
            $table->index('tracking_token');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presentation_sends');
    }
};
