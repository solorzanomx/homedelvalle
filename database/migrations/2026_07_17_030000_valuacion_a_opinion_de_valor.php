<?php

use Illuminate\Database\Migrations\Migration;

/**
 * "Valuación gratuita" → "Opinión de valor gratuita" en el contenido de BD
 * (pedido por Alejandro 2026-07-16): Home del Valle NO da valuaciones
 * gratuitas — da una opinión de valor generada por el Observatorio de
 * precios. La valuación formal la hace un valuador certificado externo y
 * tiene costo. Prometer "valuación gratuita" es prometer algo que no se da.
 *
 * Los defaults del código ya se corrigieron; esto corrige las copias que
 * viven en BD: posts del blog (body/excerpt/metas/ctas) y site_settings
 * (services_section, cta_subheading, vender_content, etc.). Además agrega
 * la FAQ "¿La opinión de valor es lo mismo que un avalúo?" si hay FAQs
 * editadas guardadas.
 */
return new class extends Migration
{
    private const REEMPLAZOS = [
        'Valuación profesional gratuita' => 'Opinión de valor gratuita',
        'valuación profesional gratuita' => 'opinión de valor gratuita',
        'Valuación gratuita'             => 'Opinión de valor gratuita',
        'valuación gratuita'             => 'opinión de valor gratuita',
        'Solicita tu valuación'          => 'Solicita tu opinión de valor',
        'Solicitar valuación'            => 'Solicitar opinión de valor',
        'solicitar una valuación'        => 'solicitar una opinión de valor',
        'tu valuación'                   => 'tu opinión de valor',
        'una valuación sin costo'        => 'una opinión de valor sin costo',
        'valuación sin costo'            => 'opinión de valor sin costo',
        'valuación profesional sin costo' => 'opinión de valor sin costo',
    ];

    private function limpiar(?string $texto): ?string
    {
        return $texto === null ? null : strtr($texto, self::REEMPLAZOS);
    }

    /** Reemplaza recursivamente en arrays (services_section, vender_content…). */
    private function limpiarArray(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $data[$key] = $this->limpiar($value);
            } elseif (is_array($value)) {
                $data[$key] = $this->limpiarArray($value);
            }
        }

        return $data;
    }

    public function up(): void
    {
        // ── Posts del blog ──
        foreach (\App\Models\Post::cursor() as $post) {
            $dirty = false;
            foreach (['body', 'excerpt', 'meta_title', 'meta_description'] as $campo) {
                $nuevo = $this->limpiar($post->{$campo});
                if ($nuevo !== $post->{$campo}) {
                    $post->{$campo} = $nuevo;
                    $dirty = true;
                }
            }
            if (is_array($post->ctas)) {
                $nuevos = $this->limpiarArray($post->ctas);
                if ($nuevos !== $post->ctas) {
                    $post->ctas = $nuevos;
                    $dirty = true;
                }
            }
            if ($dirty) {
                $post->saveQuietly(); // sin observers ni touch de published_at
            }
        }

        // ── SiteSettings: campos de texto + arrays de contenido ──
        $settings = \App\Models\SiteSetting::first();
        if ($settings) {
            $dirty = false;

            foreach (['site_tagline', 'hero_subheading', 'cta_heading', 'cta_subheading',
                      'services_heading', 'services_subheading', 'home_welcome_text',
                      'hero_cta_text', 'navbar_cta_text'] as $campo) {
                $nuevo = $this->limpiar($settings->{$campo});
                if ($nuevo !== $settings->{$campo}) {
                    $settings->{$campo} = $nuevo;
                    $dirty = true;
                }
            }

            foreach (['services_section', 'benefits_section', 'vender_content',
                      'servicios_content', 'business_model_steps', 'stats_section'] as $campo) {
                if (is_array($settings->{$campo})) {
                    $nuevo = $this->limpiarArray($settings->{$campo});
                    if ($nuevo !== $settings->{$campo}) {
                        $settings->{$campo} = $nuevo;
                        $dirty = true;
                    }
                }
            }

            // FAQ avalúo vs opinión de valor — solo si hay FAQs editadas en BD
            $content = $settings->vender_content;
            if (is_array($content) && ! empty($content['faqs']) && is_array($content['faqs'])) {
                $existe = collect($content['faqs'])->contains(
                    fn ($f) => str_contains($f['q'] ?? '', 'avalúo')
                );
                if (! $existe) {
                    $content['faqs'][] = [
                        'q' => '¿La opinión de valor es lo mismo que un avalúo?',
                        'a' => 'No. La opinión de valor es gratuita y la genera nuestro Observatorio de precios con datos reales de la zona — es la referencia con la que salimos al mercado. El avalúo formal lo realiza un valuador certificado externo, tiene costo, y solo se necesita cuando la operación lo exige (por ejemplo, crédito bancario o trámite notarial). Si tu venta lo requiere, lo coordinamos por ti.',
                    ];
                    $settings->vender_content = $content;
                    $dirty = true;
                }
            }

            if ($dirty) {
                $settings->save();
            }
        }
    }

    public function down(): void
    {
        // Sin reversa: el texto anterior prometía un servicio que no se da.
    }
};
