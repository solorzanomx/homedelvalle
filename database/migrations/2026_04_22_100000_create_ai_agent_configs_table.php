<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ai_agent_configs')) {
            return;
        }

        Schema::create('ai_agent_configs', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();         // e.g. "carousel.generation"
            $table->string('label');                 // "Generación de Carruseles"
            $table->string('description')->nullable();
            $table->string('provider');              // anthropic | perplexity | openai
            $table->string('model');
            $table->unsignedSmallInteger('max_tokens')->default(2048);
            $table->decimal('temperature', 3, 2)->default(0.70);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed default agents
        $now = now();
        DB::table('ai_agent_configs')->insert([
            [
                'key'         => 'carousel.generation',
                'label'       => 'Generación de Carruseles',
                'description' => 'Genera el contenido (slides, caption, hashtags) de cada carrusel.',
                'provider'    => 'anthropic',
                'model'       => 'claude-sonnet-4-6',
                'max_tokens'  => 4096,
                'temperature' => 0.75,
                'is_active'   => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'key'         => 'carousel.discovery.web',
                'label'       => 'Descubrimiento de Temas — Web',
                'description' => 'Busca tendencias inmobiliarias CDMX en web para proponer temas de carruseles.',
                'provider'    => 'perplexity',
                'model'       => 'sonar',
                'max_tokens'  => 2048,
                'temperature' => 0.70,
                'is_active'   => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'key'         => 'carousel.discovery.blog',
                'label'       => 'Descubrimiento de Temas — Blog',
                'description' => 'Analiza el blog propio y sugiere temas de carruseles complementarios.',
                'provider'    => 'anthropic',
                'model'       => 'claude-haiku-4-5-20251001',
                'max_tokens'  => 2048,
                'temperature' => 0.70,
                'is_active'   => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'key'         => 'market.fetch',
                'label'       => 'Precios de Mercado — Búsqueda',
                'description' => 'Busca anuncios reales en portales inmobiliarios (Paso 1 del pipeline).',
                'provider'    => 'perplexity',
                'model'       => 'sonar',
                'max_tokens'  => 1024,
                'temperature' => 0.30,
                'is_active'   => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'key'         => 'market.analysis',
                'label'       => 'Precios de Mercado — Análisis',
                'description' => 'Filtra outliers y calcula estadísticas de precio/m² (Paso 2 del pipeline).',
                'provider'    => 'anthropic',
                'model'       => 'claude-haiku-4-5-20251001',
                'max_tokens'  => 2000,
                'temperature' => 0.20,
                'is_active'   => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'key'         => 'valuation.narrative',
                'label'       => 'Opinión de Valor — Narrativa',
                'description' => 'Genera el análisis narrativo profesional para el PDF de valuación.',
                'provider'    => 'anthropic',
                'model'       => 'claude-sonnet-4-6',
                'max_tokens'  => 900,
                'temperature' => 0.65,
                'is_active'   => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_agent_configs');
    }
};
