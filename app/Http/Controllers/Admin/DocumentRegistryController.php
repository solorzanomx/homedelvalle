<?php

namespace App\Http\Controllers\Admin;

use App\Console\Commands\SeedDemoDocuments;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Operation;
use App\Models\PropertyValuation;
use App\Models\PurchaseOffer;
use App\Services\PresentationGeneratorService;
use App\Services\PurchaseOfferGeneratorService;
use App\Services\ServiciosGeneratorService;
use Illuminate\Support\Facades\Response;
use Spatie\Browsershot\Browsershot;

/**
 * Panel /admin/documentos — dónde viven los 5 documentos con identidad de
 * marca, con enlaces a vista previa (datos de ejemplo, ver
 * SeedDemoDocuments) y changelog. No hay edición libre de contenido aquí,
 * salvo las cláusulas de Carta Oferta — ver DocumentClauseController.
 */
class DocumentRegistryController extends Controller
{
    public function index()
    {
        $documents = collect(config('document_registry'))->map(function ($doc, $key) {
            $doc['key'] = $key;
            $doc['ultima_actualizacion'] = collect($doc['changelog'])->sortByDesc('fecha')->first();
            return $doc;
        });

        return view('admin.documentos.index', compact('documents'));
    }

    public function previewPresentacion(PresentationGeneratorService $generator)
    {
        $captacion = \App\Models\Captacion::where('client_id', $this->demoClient()->id)->first();

        abort_unless($captacion, 404, 'Corre "php artisan documentos:seed-demo" primero.');

        return response($generator->renderHtml($captacion));
    }

    public function previewServicios(ServiciosGeneratorService $generator)
    {
        $captacion = \App\Models\Captacion::where('client_id', $this->demoClient()->id)->first();

        abort_unless($captacion, 404, 'Corre "php artisan documentos:seed-demo" primero.');

        return response($generator->renderHtml($captacion));
    }

    public function previewOpinionValor()
    {
        $property = $this->demoClient()->ownedProperties()->first();
        $valuation = $property ? PropertyValuation::where('property_id', $property->id)->first() : null;

        abort_unless($valuation, 404, 'Corre "php artisan documentos:seed-demo" primero.');

        $valuation->load(['property', 'colonia.zone', 'adjustments']);
        $html = view('admin.valuations.pdf', compact('valuation'))->render();

        $pdf = Browsershot::html($html)
            ->setChromePath(config('browsershot.chrome_path'))
            ->setNodeBinary(config('browsershot.node_path'))
            ->setNpmBinary(config('browsershot.npm_path'))
            ->noSandbox()
            ->format('A4')
            ->pdf();

        return response($pdf, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="opinion-de-valor-muestra.pdf"',
        ]);
    }

    public function previewOfertaCompra(PurchaseOfferGeneratorService $generator)
    {
        $offer = PurchaseOffer::whereHas('operation', fn ($q) => $q->where('client_id', $this->demoClient()->id))->first();

        abort_unless($offer, 404, 'Corre "php artisan documentos:seed-demo" primero.');

        $path = $generator->generatePdf($offer);

        return Response::make(file_get_contents($path), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="oferta-compra-muestra.pdf"',
        ]);
    }

    public function ofertaCompraImprimible(PurchaseOfferGeneratorService $generator)
    {
        $path = $generator->generatePrintablePdf();

        return Response::make(file_get_contents($path), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="oferta-compra-imprimible.pdf"',
        ]);
    }

    private function demoClient(): Client
    {
        $client = Client::where('email', SeedDemoDocuments::DEMO_EMAIL)->first();
        abort_unless($client, 404, 'Corre "php artisan documentos:seed-demo" primero.');
        return $client;
    }
}
