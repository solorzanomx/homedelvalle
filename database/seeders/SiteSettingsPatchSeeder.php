<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

/**
 * Parchea erratas de tildes/acentos en los campos de site_settings.
 * Solo corrige strings incorrectos conocidos — no sobreescribe valores personalizados.
 * Idempotente: se puede ejecutar múltiples veces sin efecto secundario.
 */
class SiteSettingsPatchSeeder extends Seeder
{
    // [incorrecto => correcto]
    private array $stringFixes = [
        'Ejecutamos la operacion'                 => 'Ejecutamos la operación',
        'Negociacion, blindaje legal'             => 'Negociación, blindaje legal',
        'Negociacion y cierre'                    => 'Negociación y cierre',
        'catalogo exclusivo'                      => 'catálogo exclusivo',
        'Busqueda personalizada'                  => 'Búsqueda personalizada',
        'Analisis de inversion'                   => 'Análisis de inversión',
        'Acompanamiento legal'                    => 'Acompañamiento legal',
        'Acompanamiento hasta'                    => 'Acompañamiento hasta',
        'Mas solicitado'                          => 'Más solicitado',
        'Valuacion profesional'                   => 'Valuación profesional',
        'Marketing y fotografia'                  => 'Marketing y fotografía',
        'Anos de experiencia'                     => 'Años de experiencia',
        'Heriberto Frias'                         => 'Heriberto Frías',
    ];

    public function run(): void
    {
        $settings = SiteSetting::first();
        if (! $settings) {
            $this->command->warn('No existe registro en site_settings. Saltando.');
            return;
        }

        $changed = false;

        // -- Columnas simples (string) -----------------------------------------
        foreach (['site_name', 'address', 'business_model_heading', 'business_model_subheading',
                  'business_model_content', 'stats_heading', 'cta_heading', 'cta_subheading'] as $col) {
            if (empty($settings->$col)) continue;
            $patched = $this->patchString($settings->$col);
            if ($patched !== $settings->$col) {
                $settings->$col = $patched;
                $changed = true;
            }
        }

        // Corrección específica: site_name capitalización
        if ($settings->site_name === 'Home del valle') {
            $settings->site_name = 'Home del Valle';
            $changed = true;
        }

        // -- Columnas array/JSON -----------------------------------------------
        foreach (['business_model_steps', 'stats_section', 'services_section', 'benefits_section'] as $col) {
            if (empty($settings->$col)) continue;
            $original = $settings->$col;
            $patched  = $this->patchArray($original);
            if ($patched !== $original) {
                $settings->$col = $patched;
                $changed = true;
            }
        }

        if ($changed) {
            $settings->save();
            // Limpiar cache para que los cambios se reflejen inmediatamente
            cache()->forget('site_settings');
            $this->command->info('[OK] SiteSettingsPatchSeeder: erratas corregidas.');
        } else {
            $this->command->info('[SKIP] SiteSettingsPatchSeeder: sin erratas que corregir.');
        }
    }

    private function patchString(string $value): string
    {
        foreach ($this->stringFixes as $wrong => $correct) {
            $value = str_replace($wrong, $correct, $value);
        }
        return $value;
    }

    private function patchArray(array $arr): array
    {
        // Recorre recursivamente strings en el array
        array_walk_recursive($arr, function (&$item) {
            if (is_string($item)) {
                $item = $this->patchString($item);
            }
        });
        return $arr;
    }
}
