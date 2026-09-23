<?php

namespace App\Services;

use App\Models\RentalProcess;
use Illuminate\Support\Facades\File;
use Spatie\Browsershot\Browsershot;

/**
 * Genera el recibo PDF de la cuota de investigación — mismo patrón de
 * "página fija única" que PurchaseOfferGeneratorService::generatePrintablePdf()
 * (a diferencia del recibo de apartado, que es multipágina con cláusulas
 * legales; este es solo un recibo de pago, no un instrumento legal).
 */
class InvestigacionReceiptGeneratorService
{
    public function generatePdf(RentalProcess $rental): string
    {
        set_time_limit(120);

        $rental->loadMissing(['tenantClient', 'property']);

        $html = view('pdf.recibo-investigacion', [
            'rental'   => $rental,
            'tenant'   => $rental->tenantClient,
            'property' => $rental->property,
        ])->render();

        $dir  = storage_path('app/investigacion-receipts/' . $rental->id);
        File::ensureDirectoryExists($dir);
        $path = $dir . '/recibo-investigacion-' . $rental->id . '-' . time() . '.pdf';

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
