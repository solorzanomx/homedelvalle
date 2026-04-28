<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->enum('template_type', ['custom', 'marketing', 'newsletter', 'promotional'])->default('custom');
            $table->string('subject');
            $table->string('preview_text')->nullable();
            $table->longText('html_body');
            $table->longText('text_body')->nullable();
            $table->json('available_placeholders')->nullable();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamp('published_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['template_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_email_templates');
    }
};
