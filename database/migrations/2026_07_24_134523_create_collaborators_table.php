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
        Schema::create('collaborators', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('role');
            $table->text('bio')->nullable();
            $table->string('photo_path')->nullable();
            $table->string('link_url')->nullable();
            $table->string('link_label')->nullable();
            $table->string('email')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);

            // Autorización
            $table->string('consent_token', 64)->unique();
            $table->enum('consent_status', ['pending', 'authorized', 'declined'])->default('pending');
            $table->json('consent_snapshot')->nullable();
            $table->timestamp('consent_at')->nullable();
            $table->string('consent_ip')->nullable();
            $table->string('consent_user_agent')->nullable();
            $table->text('decline_note')->nullable();
            $table->timestamp('link_sent_at')->nullable();
            $table->timestamp('confirmation_email_sent_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collaborators');
    }
};
