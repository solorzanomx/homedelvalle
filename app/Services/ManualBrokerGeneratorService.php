<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Spatie\Browsershot\Browsershot;

/**
 * Manual del Broker — Proceso de Captación. A diferencia de Presentación /
 * Opinión de Valor / Propuesta de Servicios, este documento es contenido
 * estático (el mismo para todos los brokers, no depende de una Captacion),
 * así que no hay modelo dueño ni ruta cacheada — se renderiza en cada request.
 */
class ManualBrokerGeneratorService
{
    public function renderHtml(): string
    {
        return view('pdf.manual-broker')->render();
    }

    /** Genera el PDF y devuelve la ruta absoluta del archivo temporal. */
    public function generatePdf(): string
    {
        set_time_limit(120);

        $html = $this->renderHtml();

        $dir  = storage_path('app/manual-broker');
        File::ensureDirectoryExists($dir);
        $path = $dir . '/manual-broker-' . time() . '.pdf';

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
            ->timeout(90)
            ->savePdf($path);

        return $path;
    }
}
