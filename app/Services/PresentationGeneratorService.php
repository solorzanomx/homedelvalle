<?php

namespace App\Services;

use App\Models\Captacion;
use App\Models\ContractTemplate;
use App\Models\User;
use Illuminate\Support\Facades\File;
use Spatie\Browsershot\Browsershot;

class PresentationGeneratorService
{
    /**
     * Selecciona el ContractTemplate correcto para el intent de la captación.
     * Fallback a 'general' si no existe template para el intent exacto.
     */
    public function selectTemplate(Captacion $captacion): ?ContractTemplate
    {
        return ContractTemplate::where('type', 'presentation')
            ->where('intent_target', $captacion->intent ?? 'general')
            ->where('is_active', true)
            ->first()
            ?? ContractTemplate::where('type', 'presentation')
                ->where('intent_target', 'general')
                ->where('is_active', true)
                ->first();
    }

    /**
     * Renderiza el HTML de la presentación desde la vista Blade correspondiente.
     * $overrides permite al agente editar precio, comisión y plan en el editor (PR 5).
     */
    public function renderHtml(Captacion $captacion, array $overrides = []): string
    {
        $captacion->loadMissing(['client', 'property', 'createdBy']);

        $agent = $captacion->createdBy ?? User::find(1);

        $vars = $this->buildVars($captacion, $agent, $overrides);

        $intent = $captacion->intent ?? 'general';
        $view   = 'pdf.presentations.' . $intent;

        if (!view()->exists($view)) {
            $view = 'pdf.presentations.general';
        }

        return view($view, array_merge($vars, ['captacion' => $captacion, 'agent' => $agent]))->render();
    }

    /**
     * Genera el PDF con Browsershot y lo guarda en storage/app/presentations/{id}/.
     * Devuelve la ruta absoluta del archivo.
     */
    public function generatePdf(Captacion $captacion, array $overrides = []): string
    {
        // Browsershot + Puppeteer puede tardar 15-45s — necesitamos más que el default de 30s
        set_time_limit(120);

        $html = $this->renderHtml($captacion, $overrides);

        $dir  = storage_path('app/presentations/' . $captacion->id);
        File::ensureDirectoryExists($dir);

        $path = $dir . '/presentacion-' . time() . '.pdf';

        Browsershot::html($html)
            ->setNodeBinary(config('browsershot.node_path', '/usr/bin/node'))
            ->setChromePath(config('browsershot.chrome_path', '/usr/bin/google-chrome'))
            ->noSandbox()
            ->addChromiumArguments(['--disable-gpu', '--disable-dev-shm-usage', '--disable-extensions'])
            ->windowSize(816, 1056)   // Letter a 96dpi: 8.5" × 11"
            ->paperSize(215.9, 279.4) // Letter en mm
            ->margins(0, 0, 0, 0)
            ->showBackground()
            ->emulateMedia('screen')
            ->timeout(90)
            ->savePdf($path);

        // Guardar el path en la captación para acceso rápido
        $captacion->update(['last_presentation_pdf_path' => $path]);

        return $path;
    }

    // ─── Privados ─────────────────────────────────────────────────────────────

    private function buildVars(Captacion $captacion, ?User $agent, array $overrides): array
    {
        $client   = $captacion->client;
        $property = $captacion->property;

        $photoUrl = null;
        $media = $captacion->getMedia('property_photos')->first();
        if ($media) {
            $photoUrl = $media->getFullUrl();
        }

        // Logos desde SiteSetting (claro para fondo blanco, oscuro para fondo navy)
        $logoUrl     = null;
        $logoDarkUrl = null;
        $siteSettings = \App\Models\SiteSetting::first();
        if ($siteSettings?->logo_path) {
            $logoUrl = url('storage/' . $siteSettings->logo_path);
        }
        if ($siteSettings?->logo_path_dark) {
            $logoDarkUrl = url('storage/' . $siteSettings->logo_path_dark);
        } elseif ($logoUrl) {
            $logoDarkUrl = $logoUrl; // fallback al logo normal
        }

        // Comisión sin ceros decimales innecesarios
        $comision = $overrides['commission_pct'] ?? $captacion->commission_pct ?? 5;
        $comisionFormatted = rtrim(rtrim(number_format((float)$comision, 1, '.', ''), '0'), '.');

        return [
            'nombrePropietario' => $client?->name ?? '',
            'inmuebleTipo'      => $property ? ($property->property_type_label ?? $property->property_type) : '',
            'inmuebleColonia'   => $property?->colony ?? $captacion->property_address ?? '',
            'comisionPct'       => $comisionFormatted,
            'precioSugerido'    => $overrides['price_suggested'] ?? ($property?->price && $property->price > 0 ? '$' . number_format($property->price, 0) . ' MXN' : null),
            'planMarketing'     => $overrides['marketing_plan'] ?? $captacion->marketing_plan ?? '',
            'nombreAgente'      => $agent?->name ?? 'Home del Valle',
            'telefonoAgente'    => $agent?->phone ?? '',
            'emailAgente'       => $agent?->email ?? '',
            'fechaPresentacion' => now()->locale('es')->isoFormat('D [de] MMMM [de] YYYY'),
            'sloganHDV'         => 'Pocos inmuebles. Más control. Mejores resultados.',
            'photoUrl'          => $overrides['photo_url'] ?? $photoUrl,
            'logoUrl'           => $logoUrl,
            'logoDarkUrl'       => $logoDarkUrl,
        ];
    }
}
