<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * email_opens quedó superado por Message (trackable_type/id + external_id
 * como token + markOpened()) — un solo log de mensajes para todo en vez de
 * dos sistemas paralelos. Tabla recién creada esta misma sesión, sin datos
 * de valor en producción.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('email_opens');
    }

    public function down(): void
    {
        Schema::create('email_opens', function (Blueprint $table) {
            $table->id();
            $table->string('token', 64)->unique();
            $table->foreignId('custom_email_template_id')->nullable()->constrained()->nullOnDelete();
            $table->string('recipient_email')->nullable();
            $table->nullableMorphs('trackable');
            $table->timestamp('opened_at')->nullable();
            $table->unsignedInteger('opens_count')->default(0);
            $table->string('user_agent')->nullable();
            $table->timestamps();
        });
    }
};
