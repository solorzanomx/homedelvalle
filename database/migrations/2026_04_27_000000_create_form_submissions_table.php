<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_submissions', function (Blueprint $table) {
            $table->id();
            $table->string('form_type'); // 'vendedor', 'comprador', 'b2b', 'contacto', 'propiedad'
            $table->string('source_page'); // ruta de origen
            $table->string('full_name');
            $table->string('email');
            $table->string('phone');
            $table->json('payload'); // estructura específica según form_type
            $table->string('lead_tag')->nullable(); // LEAD_VENDEDOR, LEAD_COMPRADOR, LEAD_B2B, etc.
            $table->enum('status', ['new', 'contacted', 'qualified', 'won', 'lost'])->default('new');
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->string('referrer')->nullable();
            $table->ipAddress('ip')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('contacted_at')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['form_type', 'status']);
            $table->index('lead_tag');
            $table->index('created_at');
            $table->index('assigned_to');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_submissions');
    }
};
