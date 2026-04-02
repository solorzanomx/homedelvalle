<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();     // sender
            $table->foreignId('enrollment_id')->nullable()->constrained('automation_enrollments')->nullOnDelete();
            $table->string('channel', 20);       // email, whatsapp
            $table->string('direction', 10)->default('outbound'); // outbound, inbound
            $table->string('subject')->nullable();
            $table->text('body');
            $table->string('status', 20)->default('queued'); // queued, sent, delivered, opened, replied, failed, bounced
            $table->string('external_id')->nullable();       // tracking ID / WhatsApp message ID
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('replied_at')->nullable();
            $table->unsignedSmallInteger('open_count')->default(0);
            $table->json('metadata')->nullable();            // template vars, attachments, etc.
            $table->timestamps();

            $table->index(['client_id', 'channel']);
            $table->index(['status', 'channel']);
            $table->index('external_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
