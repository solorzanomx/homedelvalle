<?php

namespace App\Services;

use App\Models\RentalProcess;
use App\Support\NumeroALetras;
use Illuminate\Support\Facades\File;
use Spatie\Browsershot\Browsershot;

class RentalDepositReceiptGeneratorService
{
    public function renderHtml(RentalProcess $rental): string
    {
        $rental->loadMissing('tenantClient', 'property', 'user');

        $folio = 'RA-' . str_pad((string) $rental->id, 5, '0', STR_PAD_LEFT);
        $fecha = ($rental->apartado_paid_at ?? now())->locale('es')->isoFormat('D [de] MMMM [de] YYYY');

        ['buyerName' => $tenantName] = PurchaseOfferGeneratorService::buyerInfo($rental->tenantClient);
        $tenantName = $tenantName ?: '—';

        ['propertyFull' => $propertyFull] = PurchaseOfferGeneratorService::propertyInfo($rental->property);
        $propertyFull = $propertyFull ?: ($rental->property?->title ?? '—');

        $montoNumero = number_format((float) $rental->apartado_amount, 2);
        $montoLetras = NumeroALetras::pesos((float) $rental->apartado_amount);

        $metodoLabel = RentalProcess::APARTADO_PAYMENT_METHODS[$rental->apartado_payment_method] ?? $rental->apartado_payment_method;
        $recibeName = $rental->user?->full_name ?? $rental->user?->name ?? 'Home del Valle Bienes Raíces';

        return view('pdf.recibo-apartado', compact(
            'rental', 'folio', 'fecha', 'tenantName', 'propertyFull',
            'montoNumero', 'montoLetras', 'metodoLabel', 'recibeName'
        ))->render();
    }

    public function generatePdf(RentalProcess $rental): string
    {
        set_time_limit(120);

        $html = $this->renderHtml($rental);

        $dir  = storage_path('app/rental-receipts/' . $rental->id);
        File::ensureDirectoryExists($dir);
        $path = $dir . '/apartado-' . $rental->id . '-' . time() . '.pdf';

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
