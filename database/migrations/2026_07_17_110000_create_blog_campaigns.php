<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Campañas de contenido del blog: brief → mapa de temas aprobado → producción
 * con colchón de borradores → OK humano → calendario de publicación.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('objetivo')->nullable();
            $table->string('status')->default('draft'); // draft|active|paused|done
            $table->unsignedTinyInteger('posts_per_week')->default(7);
            $table->unsignedTinyInteger('buffer')->default(3); // borradores listos por adelantado
            $table->string('publish_hour', 5)->default('08:00');
            $table->text('mezcla')->nullable();   // proporciones de funnels (texto para el prompt)
            $table->text('lecciones')->nullable(); // bitácora editorial acumulada (motivos de descarte, instrucciones)
            $table->json('topics')->nullable();   // [{title, keywords, categoria, description, status: pending|generated|discarded, post_id}]
            $table->date('started_at')->nullable();
            $table->timestamps();
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->foreignId('blog_campaign_id')->nullable()->constrained('blog_campaigns')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('blog_campaign_id');
        });
        Schema::dropIfExists('blog_campaigns');
    }
};
