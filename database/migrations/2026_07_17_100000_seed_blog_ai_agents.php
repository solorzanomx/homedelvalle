<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Agentes del generador de blog (rehabilitación 2026-07-17): descubrimiento
 * de temas y generación de posts, editables en Admin → Agentes IA como el
 * resto del sistema.
 */
return new class extends Migration
{
    public function up(): void
    {
        $agentes = [
            [
                'key'         => 'blog.topics',
                'label'       => 'Blog — Descubrimiento de Temas',
                'description' => 'Propone temas de artículos alineados a la estrategia (mezcla de funnels, sin canibalizar posts existentes).',
                'provider'    => 'anthropic',
                'model'       => 'claude-sonnet-4-6',
                'max_tokens'  => 4096,
                'temperature' => 0.65,
            ],
            [
                'key'         => 'blog.generation',
                'label'       => 'Blog — Generación de Posts',
                'description' => 'Redacta el artículo completo (cuerpo, metas, CTAs, categoría, tags, prompts de imagen) bajo el canon editorial.',
                'provider'    => 'anthropic',
                'model'       => 'claude-sonnet-4-6',
                'max_tokens'  => 8192,
                'temperature' => 0.7,
            ],
        ];

        foreach ($agentes as $agente) {
            DB::table('ai_agent_configs')->updateOrInsert(
                ['key' => $agente['key']],
                $agente + ['is_active' => true, 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    public function down(): void
    {
        DB::table('ai_agent_configs')->whereIn('key', ['blog.topics', 'blog.generation'])->delete();
    }
};
