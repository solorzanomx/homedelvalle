<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Auditoría de conversión del blog (2026-09-28): `isr-venta-propiedad-heredada-mexico-2026` es el
 * post #1 de tráfico de TODO el blog (4,242 vistas) y responde justo la pregunta que la calculadora
 * de costo de sucesión resuelve ("¿cuánto me va a costar?") — pero no la tenía. El único lead real
 * capturado con la calculadora en 90 días vino del otro post de herencias que sí la tiene
 * (`cuanto-cuesta-sucesion-cdmx-2026`, ver docs/funcionalidades/blog-optimizaciones-post-lanzamiento.md).
 *
 * `isr-venta-propiedad-heredada-mexico-2026` ya es un slug "decidido" (BlogCluster::showsSellCta
 * devuelve true) — activarle la calculadora no compite con su enfoque (ISR) porque la calculadora
 * vive DESPUÉS del contenido de ISR, como el siguiente paso natural ("ya sé el ISR, ¿y el resto de
 * los costos de la sucesión?").
 */
return new class extends Migration
{
    private const SLUG = 'isr-venta-propiedad-heredada-mexico-2026';

    public function up(): void
    {
        DB::table('posts')->where('slug', self::SLUG)->update(['show_succession_calculator' => true]);
    }

    public function down(): void
    {
        DB::table('posts')->where('slug', self::SLUG)->update(['show_succession_calculator' => false]);
    }
};
