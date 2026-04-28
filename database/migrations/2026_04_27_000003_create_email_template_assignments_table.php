<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_template_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('custom_email_templates')->onDelete('cascade');
            $table->enum('trigger_type', ['event', 'form_submission', 'user_action'])->default('event');
            $table->string('trigger_name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['template_id', 'trigger_name']);
            $table->index(['trigger_type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_template_assignments');
    }
};
