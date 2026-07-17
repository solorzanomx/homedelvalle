<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Agente 'leads.classification' para el clasificador de leads (contacto +
 * portales). El clasificador usaba Gemini con key propia (GEMINI_API_KEY)
 * que NO existe en producción — por eso "no clasificaba" (y el fallback
 * silencioso lo ocultaba). Se migra al sistema de Agentes IA existente:
 * keys de Anthropic ya operando en producción (Observatorio/carruseles) y
 * modelo editable desde Admin → Agentes IA (adiós retiros de modelo en código).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('ai_agent_configs')->updateOrInsert(
            ['key' => 'leads.classification'],
            [
                'label'       => 'Clasificación de Leads',
                'description' => 'Clasifica leads del sitio y de portales (rol, temperatura, urgencia y resumen).',
                'provider'    => 'anthropic',
                'model'       => 'claude-haiku-4-5-20251001',
                'max_tokens'  => 300,
                'temperature' => 0,
                'is_active'   => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('ai_agent_configs')->where('key', 'leads.classification')->delete();
    }
};
