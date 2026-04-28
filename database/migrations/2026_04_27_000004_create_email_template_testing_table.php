<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_template_testing', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('custom_email_templates')->onDelete('cascade');
            $table->string('test_email');
            $table->json('test_data')->nullable();
            $table->enum('status', ['sent', 'failed'])->default('sent');
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['template_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_template_testing');
    }
};
