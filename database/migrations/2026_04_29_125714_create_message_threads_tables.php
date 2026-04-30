<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    /**
     * Fase 1-E: Mensajería bidireccional cliente ↔ HDV.
     * Crea message_threads y message_thread_messages.
     * Comunicación siempre pasa por HDV (no chat directo entre partes).
     */
    public function up(): void
    {
        if (! Schema::hasTable('message_threads')) {
            Schema::create('message_threads', function (Blueprint $table) {
                $table->id();
                $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
                $table->foreignId('operation_id')->nullable()->constrained('operations')->nullOnDelete();
                $table->foreignId('rental_process_id')->nullable()->constrained('rental_processes')->nullOnDelete();
                $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete()
                    ->comment('Agente HDV responsable del thread');
                $table->string('subject')->nullable();
                $table->enum('status', ['open', 'closed', 'archived'])->default('open');
                $table->timestamp('last_message_at')->nullable();
                $table->unsignedInteger('unread_by_client')->default(0);
                $table->unsignedInteger('unread_by_hdv')->default(0);
                $table->timestamps();

                $table->index(['client_id', 'status']);
                $table->index('last_message_at');
            });
        }

        if (! Schema::hasTable('message_thread_messages')) {
            Schema::create('message_thread_messages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('thread_id')->constrained('message_threads')->cascadeOnDelete();
                $table->string('author_type', 50)->comment('client | user');
                $table->unsignedBigInteger('author_id')->nullable()->comment('clients.id o users.id');
                $table->text('body');
                $table->enum('type', ['text', 'system_event', 'attachment'])->default('text');
                $table->string('attachment_path')->nullable();
                $table->string('attachment_name')->nullable();
                $table->timestamp('read_at')->nullable()->comment('Cuándo fue leído por la otra parte');
                $table->timestamps();

                $table->index(['thread_id', 'created_at']);
                $table->index(['author_type', 'author_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('message_thread_messages');
        Schema::dropIfExists('message_threads');
    }
};
