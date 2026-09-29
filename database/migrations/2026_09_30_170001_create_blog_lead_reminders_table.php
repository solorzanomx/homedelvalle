<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Optimización post-lanzamiento: seguimiento automático para leads del blog que no avanzan
 * (docs/funcionalidades/blog-leads-panel.md). Antes un heredero en "etapa temprana" que no
 * respondía se enfriaba solo — nadie le recordaba al asesor volver a intentar.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_lead_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_submission_id')->constrained()->cascadeOnDelete();
            $table->string('tier', 10);   // 'd7' | 'd14'
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['form_submission_id', 'tier']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_lead_reminders');
    }
};
