<?php

namespace App\Services;

use App\Models\HelpCategory;
use Illuminate\Support\Facades\File;
use Spatie\Browsershot\Browsershot;

/**
 * Manual de Operación — el centro de ayuda completo (artículos publicados,
 * agrupados por categoría) como PDF descargable con la marca. Complemento
 * imprimible del centro de ayuda para onboarding de gente nueva.
 *
 * Mismo patrón que ManualBrokerGeneratorService: contenido dinámico desde
 * help_articles, sin caché (el manual cambia cada vez que se edita un
 * artículo).
 */
class ManualOperacionGeneratorService
{
    public function renderHtml(): string
    {
        $categories = HelpCategory::with(['articles' => fn ($q) => $q->where('is_published', true)->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get()
            ->filter(fn ($c) => $c->articles->isNotEmpty())
            ->values();

        return view('pdf.manual-operacion', ['categories' => $categories])->render();
    }

    /** Genera el PDF y devuelve la ruta absoluta del archivo temporal. */
    public function generatePdf(): string
    {
        set_time_limit(180);

        $html = $this->renderHtml();

        $dir  = storage_path('app/manual-operacion');
        File::ensureDirectoryExists($dir);
        $path = $dir . '/manual-operacion-' . time() . '.pdf';

        Browsershot::html($html)
            ->setNodeBinary(config('browsershot.node_path', '/usr/bin/node'))
            ->setChromePath(config('browsershot.chrome_path', '/usr/bin/google-chrome'))
            ->noSandbox()
            ->addChromiumArguments(['--disable-gpu', '--disable-dev-shm-usage', '--disable-extensions'])
            ->windowSize(816, 1056)
            ->paperSize(215.9, 279.4)
            ->landscape(false)
            ->margins(0, 0, 0, 0)
            ->showBackground()
            ->emulateMedia('screen')
            ->timeout(120)
            ->savePdf($path);

        return $path;
    }
}
