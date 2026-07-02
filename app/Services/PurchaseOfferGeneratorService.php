<?php

namespace App\Services;

use App\Models\Operation;
use App\Models\PurchaseOffer;
use App\Support\NumeroALetras;
use Illuminate\Support\Facades\File;
use Spatie\Browsershot\Browsershot;

class PurchaseOfferGeneratorService
{
    public function renderHtml(PurchaseOffer $offer): string
    {
        $offer->loadMissing('operation.client', 'operation.property');
        $operation = $offer->operation;
        $client    = $operation->client;
        $property  = $operation->property;

        $folio = 'CO-' . str_pad((string) $offer->id, 5, '0', STR_PAD_LEFT);
        $fecha = $offer->offered_at->locale('es')->isoFormat('D [de] MMMM [de] YYYY');
        $vigenciaHasta = $offer->vigente_hasta->locale('es')->isoFormat('D [de] MMMM [de] YYYY');

        $buyerName = trim(implode(' ', array_filter([
            $client?->first_name ?: $client?->name,
            $client?->last_name_paterno,
            $client?->last_name_materno,
        ]))) ?: ($client?->name ?? '—');

        $buyerId = $client?->id_type && $client?->id_number
            ? "{$client->id_type} {$client->id_number}"
            : null;

        $buyerCurpRfc = collect([
            $client?->curp ? "CURP: {$client->curp}" : null,
            $client?->rfc ? "RFC: {$client->rfc}" : null,
        ])->filter()->implode(' · ') ?: null;

        $buyerAddress = collect([
            $client?->address_street,
            $client?->address_colony,
            $client?->address_municipality,
            $client?->address_state,
            $client?->address_zip,
        ])->filter()->implode(', ') ?: null;

        $propertyAddress = $property?->address ?: ($property?->colony . ', ' . $property?->city);
        $propertyExtra = collect([
            $property?->colony ? "Colonia {$property->colony}" : null,
            $property?->city,
            $property?->area ? "{$property->area} m²" : null,
        ])->filter()->implode(', ') ?: null;

        $precioLetras = NumeroALetras::pesos((float) $offer->precio_ofertado);

        return view('pdf.oferta-compra', compact(
            'offer', 'operation', 'client', 'property', 'folio', 'fecha', 'vigenciaHasta',
            'buyerName', 'buyerId', 'buyerCurpRfc', 'buyerAddress',
            'propertyAddress', 'propertyExtra', 'precioLetras'
        ))->render();
    }

    public function generatePdf(PurchaseOffer $offer): string
    {
        set_time_limit(120);

        $html = $this->renderHtml($offer);

        $dir  = storage_path('app/purchase-offers/' . $offer->operation_id);
        File::ensureDirectoryExists($dir);
        $path = $dir . '/oferta-' . $offer->id . '-' . time() . '.pdf';

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

        $offer->update(['last_pdf_path' => $path]);

        return $path;
    }
}
