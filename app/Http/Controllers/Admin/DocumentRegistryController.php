<?php

namespace App\Http\Controllers\Admin;

use App\Console\Commands\SeedDemoDocuments;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Operation;
use App\Models\Property;
use App\Models\PropertyValuation;
use App\Models\PurchaseOffer;
use App\Services\PresentationGeneratorService;
use App\Services\PurchaseOfferGeneratorService;
use App\Services\ServiciosGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

    /**
     * Versión imprimible en blanco — el broker puede opcionalmente elegir un
     * Cliente y/o Property del CRM para prellenar la identificación del
     * oferente y el inmueble; precio/apartado/pagos/fecha siempre quedan en
     * blanco, se llenan a mano en el momento.
     */
    public function ofertaCompraImprimibleForm()
    {
        $clients = Client::orderBy('name')->get(['id', 'name', 'email']);
        $properties = Property::orderBy('address')->get(['id', 'address', 'colony', 'city']);

        return view('admin.documentos.oferta-compra-imprimible-form', compact('clients', 'properties'));
    }

    public function ofertaCompraImprimibleGenerate(Request $request, PurchaseOfferGeneratorService $generator)
    {
        $validated = $request->validate([
            'client_id'   => 'nullable|exists:clients,id',
            'property_id' => 'nullable|exists:properties,id',
        ]);

        $client = !empty($validated['client_id']) ? Client::find($validated['client_id']) : null;
        $property = !empty($validated['property_id']) ? Property::find($validated['property_id']) : null;

        $path = $generator->generatePrintablePdf($client, $property);

        return Response::make(file_get_contents($path), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="oferta-compra-imprimible.pdf"',
        ]);
    }

    /**
     * Generar Carta Oferta "flash" — para cuando el cliente y el inmueble ya
     * existen en el sistema pero todavía no hay Operation/pipeline abierto
     * (ej. alguien que solo está dado de alta como cliente y decide ofertar
     * el mismo día). Crea por detrás la Operation mínima necesaria — el
     * broker no la ve ni la gestiona, solo llena los datos de la oferta.
     */
    public function flashForm()
    {
        $clients = Client::orderBy('name')->get(['id', 'name', 'email', 'phone']);
        $properties = Property::orderBy('address')->get(['id', 'address', 'colony', 'city']);

        return view('admin.documentos.oferta-compra-flash', compact('clients', 'properties'));
    }

    public function flashStore(Request $request, PurchaseOfferGeneratorService $generator, \App\Services\OperationChecklistService $checklistService)
    {
        $validated = $request->validate([
            'client_id'            => 'required|exists:clients,id',
            'property_id'          => 'required|exists:properties,id',
            'precio_ofertado'      => 'required|numeric|min:0',
            'monto_apartado'       => 'nullable|numeric|min:0',
            'pago_firma_contrato'  => 'nullable|numeric|min:0',
            'pago_firma_escritura' => 'nullable|numeric|min:0',
            'forma_pago'           => 'nullable|string|max:255',
            'vigencia_dias'        => 'required|integer|min:8|max:90',
            'comentarios'          => 'nullable|string|max:2000',
        ]);

        // Reutiliza una Operation existente de este cliente+inmueble si ya hay una
        // (ej. si ya se generó otra oferta antes) — evita duplicar de más.
        $operation = Operation::firstOrCreate(
            ['type' => 'venta', 'client_id' => $validated['client_id'], 'property_id' => $validated['property_id']],
            [
                'target_type' => 'venta',
                'phase'       => 'operacion',
                'stage'       => 'candidatos',
                'status'      => 'active',
                'user_id'     => Auth::id(),
                'amount'      => $validated['precio_ofertado'],
                'currency'    => 'MXN',
            ]
        );

        // Si se reutilizó una Operation existente que todavía no había llegado
        // a "candidatos" (ej. seguía en publicacion), la oferta la empuja ahí.
        $order = array_flip(Operation::VENTA_STAGES);
        if (($order[$operation->stage] ?? 0) < ($order['candidatos'] ?? 0)) {
            $checklistService->changeStage($operation, 'candidatos', Auth::user(), 'Oferta recibida (flash)');
        }

        $offer = PurchaseOffer::create($validated + [
            'operation_id' => $operation->id,
            'offered_at'   => now(),
        ]);

        $path = $generator->generatePdf($offer);

        \App\Models\Document::create([
            'operation_id' => $operation->id,
            'client_id'    => $operation->client_id,
            'uploaded_by'  => Auth::id(),
            'category'     => 'oferta_compra',
            'label'        => 'Carta Oferta de Compra (flash) — ' . now()->format('d/m/Y'),
            'file_path'    => $path,
            'file_name'    => 'CO-' . str_pad((string) $offer->id, 5, '0', STR_PAD_LEFT) . '.pdf',
            'mime_type'    => 'application/pdf',
            'file_size'    => file_exists($path) ? filesize($path) : null,
            'status'       => 'verified',
        ]);

        return Response::make(file_get_contents($path), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="oferta-compra.pdf"',
        ]);
    }

    private function demoClient(): Client
    {
        $client = Client::where('email', SeedDemoDocuments::DEMO_EMAIL)->first();
        abort_unless($client, 404, 'Corre "php artisan documentos:seed-demo" primero.');
        return $client;
    }
}
