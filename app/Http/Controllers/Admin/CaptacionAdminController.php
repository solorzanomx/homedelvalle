<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Captacion;
use App\Models\Document;
use App\Models\Operation;
use App\Models\PropertyValuation;
use App\Models\User;
use App\Services\CaptacionDeclineService;
use App\Services\CaptacionService;
use App\Services\ClientPortalService;
use App\Services\ManualBrokerGeneratorService;
use App\Services\PresentationGeneratorService;
use App\Services\ServiciosGeneratorService;
use App\Services\VisitSchedulingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class CaptacionAdminController extends Controller
{
    public function __construct(protected CaptacionService $service) {}

    public function createFromCall()
    {
        return view('admin.captaciones.create-from-call');
    }

    /** Manual del Broker — documento estático, mismo contenido para todos. */
    public function manualBroker(ManualBrokerGeneratorService $generator)
    {
        set_time_limit(120);
        $path = $generator->generatePdf();

        return Response::make(file_get_contents($path), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="manual-broker-hdv.pdf"',
        ]);
    }

    public function manualBrokerDownload(ManualBrokerGeneratorService $generator)
    {
        set_time_limit(120);
        $path = $generator->generatePdf();

        return Response::download($path, 'HDV-Manual-del-Broker.pdf', [
            'Content-Type' => 'application/pdf',
        ]);
    }

    public function presentation(Captacion $captacion)
    {
        // Cargar la página INMEDIATAMENTE — el iframe dispara la generación por su cuenta
        $captacion->loadMissing(['client', 'property', 'createdBy']);
        return view('admin.captaciones.presentation', compact('captacion'));
    }

    public function presentationPdf(Captacion $captacion)
    {
        set_time_limit(120);
        $captacion->loadMissing(['client', 'property', 'createdBy']);

        if (empty($captacion->last_presentation_pdf_path) || !file_exists($captacion->last_presentation_pdf_path)) {
            // generatePdf() ya hace $captacion->update() que actualiza el modelo en memoria.
            // NO llamar refresh() — recarga relaciones incluyendo 'media' que falla en producción.
            $path = app(PresentationGeneratorService::class)->generatePdf($captacion);
        }

        $path = $captacion->last_presentation_pdf_path;

        return Response::make(file_get_contents($path), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="presentacion-hdv.pdf"',
        ]);
    }

    public function presentationRegenerate(Captacion $captacion)
    {
        try {
            app(PresentationGeneratorService::class)->generatePdf($captacion);
        } catch (\Throwable $e) {
            return back()->with('error', 'Error al regenerar: ' . $e->getMessage());
        }

        return back()->with('success', 'Presentación regenerada correctamente.');
    }

    public function presentationDownload(Captacion $captacion)
    {
        $captacion->loadMissing(['client']);

        if (empty($captacion->last_presentation_pdf_path) || !file_exists($captacion->last_presentation_pdf_path)) {
            app(PresentationGeneratorService::class)->generatePdf($captacion);
        }

        $filename = 'HDV-Presentacion-' . str_replace(' ', '-', $captacion->client->name) . '.pdf';

        return Response::download($captacion->last_presentation_pdf_path, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    public function sendPresentationEmail(Request $request, Captacion $captacion)
    {
        $request->validate(['email' => 'required|email']);

        $captacion->loadMissing(['client', 'property', 'createdBy']);

        try {
            app(PresentationGeneratorService::class)->sendByEmail(
                captacion: $captacion,
                email:     $request->input('email'),
                agent:     Auth::user(),
            );
        } catch (\Throwable $e) {
            return back()->with('error', 'Error al enviar email: ' . $e->getMessage());
        }

        $this->maybeCreatePortalAccount($captacion);

        return back()->with('success', 'Presentación enviada por email a ' . $request->input('email'));
    }

    public function sendPresentationWhatsApp(Request $request, Captacion $captacion)
    {
        $request->validate(['phone' => 'required|string|max:30']);

        $captacion->loadMissing(['client', 'property', 'createdBy']);

        try {
            $result = app(PresentationGeneratorService::class)->sendByWhatsApp(
                captacion: $captacion,
                phone:     $request->input('phone'),
                agent:     Auth::user(),
            );
        } catch (\Throwable $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }

        $this->maybeCreatePortalAccount($captacion);

        // Redirigir al wa.me — el agente envía desde su WhatsApp Desktop
        return redirect($result['wa_me_url']);
    }

    /**
     * Al enviar la Presentación es el momento en que el propietario entra en
     * contacto real por primera vez — la cuenta del portal nace aquí, no
     * hasta firmar la exclusiva, para que pueda seguir todo el proceso desde
     * el día 1. Idempotente (ClientPortalService::createPortalAccount ya
     * revisa si el cliente ya tiene cuenta).
     */
    private function maybeCreatePortalAccount(Captacion $captacion): void
    {
        if (! config('portal.auto_create_accounts', false)) {
            return;
        }

        try {
            $portal = app(ClientPortalService::class);
            $result = $portal->createPortalAccount($captacion->client);

            if ($result['password']) {
                $portal->sendWelcomeInvitation($result['user']);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('maybeCreatePortalAccount: falló', [
                'captacion_id' => $captacion->id,
                'error'        => $e->getMessage(),
            ]);
        }
    }

    public function show(Captacion $captacion)
    {
        $captacion->load(['client', 'documents.uploader', 'valuation', 'signatureRequest', 'operation.checklistItems.template', 'operation.checklistItems.completedByUser']);

        $allCategories  = Document::CATEGORIES;
        $requiredCats   = Captacion::REQUIRED_DOCS_ETAPA1;
        $optionalCats   = Captacion::OPTIONAL_DOCS_ETAPA1;
        $docsByCategory = $captacion->documents->groupBy('category');

        // Valuaciones vinculadas a propiedades del cliente
        $propertyIds = \App\Models\Property::where('client_id', $captacion->client_id)->pluck('id');
        $valuations  = PropertyValuation::whereIn('property_id', $propertyIds)->with('colonia')->latest()->get();

        // Propiedad del cliente para pre-llenar valuacion
        $clientProperty = \App\Models\Property::where('client_id', $captacion->client_id)
            ->with('marketColonia.zone')
            ->latest()
            ->first();

        // Timeline: interactions de este proceso (esta propiedad) + las
        // genéricas sin propiedad asignada — evita mezclar el historial de
        // otras captaciones/propiedades del mismo cliente. Ver
        // docs/07-FLUJO-CAPTACION-Y-MEJORAS.md.
        $interactions = \App\Models\Interaction::where('client_id', $captacion->client_id)
            ->where(function ($q) use ($captacion) {
                $q->whereNull('property_id')->orWhere('property_id', $captacion->property_id);
            })
            ->with('user')
            ->latest()
            ->take(30)
            ->get();

        $etapaLabels = [1 => 'Documentación', 2 => 'Valuación', 3 => 'Precio', 4 => 'Exclusiva'];
        $etapaColors = [1 => '#f59e0b', 2 => '#3b82f6', 3 => '#8b5cf6', 4 => '#10b981'];

        // Brief pre-visita: precio de referencia del Observatorio para la
        // colonia del inmueble. Ver docs/07-FLUJO-CAPTACION-Y-MEJORAS.md.
        $marketSnapshot = app(PresentationGeneratorService::class)->getMarketSnapshot($captacion);

        return view('admin.captaciones.show', compact(
            'captacion', 'allCategories', 'requiredCats', 'optionalCats',
            'docsByCategory', 'valuations', 'interactions', 'etapaLabels', 'etapaColors',
            'clientProperty', 'marketSnapshot'
        ));
    }

    /**
     * Atajo de un clic para agendar la visita de captación desde la ficha,
     * sin ir al perfil del cliente. Ver docs/07-FLUJO-CAPTACION-Y-MEJORAS.md
     * sección 2.3 — reusa VisitSchedulingService (mismo mecanismo de
     * confirmación/recordatorio/reagendado ya probado en producción).
     */
    public function scheduleVisit(Request $request, Captacion $captacion)
    {
        $validated = $request->validate([
            'scheduled_at_date' => 'required|date',
            'scheduled_at_time' => 'nullable|date_format:H:i',
        ]);

        $scheduledAt = \Carbon\Carbon::parse($validated['scheduled_at_date'] . ' ' . ($validated['scheduled_at_time'] ?? '10:00'));

        app(VisitSchedulingService::class)->createVisit(
            client: $captacion->client,
            property: $captacion->property,
            broker: Auth::user(),
            scheduledAt: $scheduledAt,
            description: 'Visita de captación agendada desde el pipeline.',
        );

        return back()->with('success', 'Visita agendada. Se envió la confirmación al propietario.');
    }

    public function updateDocStatus(Request $request, Captacion $captacion, Document $document)
    {
        $request->validate(['captacion_status' => 'required|in:aprobado,rechazado,pendiente', 'rejection_reason' => 'nullable|string|max:500']);

        if ($request->captacion_status === 'aprobado') {
            $this->service->approveDocument($document);
        } elseif ($request->captacion_status === 'rechazado') {
            $this->service->rejectDocument($document, $request->input('rejection_reason'));
        } else {
            $document->update(['captacion_status' => 'pendiente', 'rejection_reason' => null]);
        }

        $this->service->recalculateStage($captacion);

        return back()->with('success', 'Estado del documento actualizado.');
    }

    public function linkValuation(Request $request, Captacion $captacion)
    {
        $request->validate(['valuation_id' => 'required|exists:property_valuations,id']);

        $this->service->linkValuation($captacion, $request->input('valuation_id'));

        return back()->with('success', 'Valuación vinculada. El cliente avanza a la etapa de precio.');
    }

    public function unlinkValuation(Captacion $captacion)
    {
        $captacion->update([
            'etapa3_valuation_id'  => null,
            'etapa2_completed_at'  => null,
            'portal_etapa'         => 2,
        ]);

        $this->service->recalculateStage($captacion);

        return back()->with('success', 'Valuación desvinculada.');
    }

    public function setPrice(Request $request, Captacion $captacion)
    {
        $request->validate(['precio' => 'required|numeric|min:1']);

        $this->service->confirmPrice($captacion, $request->input('precio'));

        return back()->with('success', 'Precio establecido. El cliente podrá confirmarlo en su portal.');
    }

    public function generarExclusiva(Request $request, Captacion $captacion)
    {
        if (!$captacion->precio_acordado) {
            return back()->with('error', 'Establece el precio antes de generar el contrato de exclusiva.');
        }

        $validated = $request->validate(['vigencia_dias' => 'nullable|integer|min:90|max:365']);
        $vigenciaDias = $validated['vigencia_dias'] ?? 180;

        try {
            $action = app(\App\Actions\Contracts\GenerarContratoExclusivaAction::class);
            $signatureRequest = $action->execute($captacion->client, $captacion, $vigenciaDias);
            $this->service->linkExclusiva($captacion, $signatureRequest->id);
        } catch (\Throwable $e) {
            return back()->with('error', 'Error al generar contrato: ' . $e->getMessage());
        }

        return back()->with('success', 'Contrato de exclusiva generado correctamente.');
    }

    public function markExclusivaSigned(Captacion $captacion)
    {
        if (!$captacion->etapa4_signature_id) {
            return back()->with('error', 'No hay contrato de exclusiva generado.');
        }

        $captacion->signatureRequest->update(['status' => 'completed', 'completed_at' => now()]);
        $this->service->recalculateStage($captacion);

        return back()->with('success', 'Contrato marcado como firmado. Proceso completado.');
    }

    /** Ver/descargar el PDF del contrato de exclusiva ya generado. */
    public function exclusivaPdf(Captacion $captacion)
    {
        $path = $captacion->signatureRequest?->local_pdf_path;

        if (empty($path) || !file_exists($path)) {
            abort(404, 'PDF no encontrado.');
        }

        return \Illuminate\Support\Facades\Response::make(file_get_contents($path), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="contrato-exclusiva.pdf"',
        ]);
    }

    public function uploadDocument(Request $request, Captacion $captacion)
    {
        $request->validate([
            'category' => 'required|string',
            'file'     => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx',
        ]);

        $file = $request->file('file');
        $path = $file->store('documents/captacion-' . $captacion->id, 'public');

        Document::create([
            'captacion_id'     => $captacion->id,
            'client_id'        => $captacion->client_id,
            'uploaded_by'      => \Illuminate\Support\Facades\Auth::id(),
            'category'         => $request->category,
            'label'            => $file->getClientOriginalName(),
            'file_path'        => $path,
            'file_name'        => $file->getClientOriginalName(),
            'mime_type'        => $file->getMimeType(),
            'file_size'        => $file->getSize(),
            'captacion_status' => 'pendiente',
            'status'           => 'received',
        ]);

        $this->service->recalculateStage($captacion);

        return back()->with('success', 'Documento subido correctamente.');
    }

    public function deleteDocument(Captacion $captacion, Document $document)
    {
        if ($document->captacion_id !== $captacion->id) abort(403);

        if ($document->file_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($document->file_path)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();
        $this->service->recalculateStage($captacion);

        return back()->with('success', 'Documento eliminado.');
    }

    public function declineCaptacion(Request $request, Captacion $captacion)
    {
        if ($captacion->status === 'declinado') {
            return back()->with('error', 'Este caso ya fue declinado.');
        }

        $request->validate(['reason' => 'required|string|min:10|max:1000']);

        app(CaptacionDeclineService::class)->decline($captacion, $request->reason, Auth::user());

        return back()->with('success', 'Caso declinado. ' . ($captacion->client?->email ? 'Se envió un email amistoso al propietario.' : 'No se envió email (propietario sin email registrado).'));
    }

    /** Pipeline de prospección: Operations type=captacion por etapa */
    public function pipeline(Request $request)
    {
        $query = Operation::with(['client', 'property', 'user'])
            ->where('type', 'captacion')
            ->where('status', 'active');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('q')) {
            $q = $request->q;
            $query->whereHas('client', fn($cq) => $cq->where('name', 'like', "%{$q}%"));
        }

        $operations = $query->latest()->get();

        $stages      = array_combine(Operation::CAPTACION_STAGES, array_map(fn($s) => Operation::STAGES[$s] ?? $s, Operation::CAPTACION_STAGES));
        $stageColors = array_intersect_key(Operation::STAGE_COLORS, $stages);
        $byStage     = $operations->groupBy('stage');

        // Captaciones activas para cruzar con operations (mostrar si ya tienen captacion_id)
        $captacionIds = Captacion::whereIn('operation_id', $operations->pluck('id'))
            ->pluck('id', 'operation_id');

        $stats = [
            'total'               => $operations->count(),
            'converted_this_month'=> Captacion::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
            'declined_this_month' => Captacion::where('status', 'declinado')->whereMonth('updated_at', now()->month)->whereYear('updated_at', now()->year)->count(),
        ];

        $users       = User::orderBy('name')->get();
        $currentUser = $request->input('user_id');

        return view('admin.captaciones.pipeline', compact(
            'byStage', 'stages', 'stageColors', 'stats', 'users', 'captacionIds', 'currentUser'
        ));
    }

    /**
     * Propuesta de Servicios en modo "presentación en vivo" — el mismo HTML
     * de renderHtml() servido para mostrarse en tablet/laptop frente al
     * propietario durante la visita, en vez de solo mandar el PDF después.
     * Ver docs/07-FLUJO-CAPTACION-Y-MEJORAS.md sección 3.
     */
    public function serviciosLive(Captacion $captacion)
    {
        $captacion->loadMissing(['client', 'property', 'createdBy']);
        $html = app(ServiciosGeneratorService::class)->renderHtml($captacion);

        // renderHtml() devuelve un documento HTML completo (pensado para PDF),
        // no un fragmento — se sirve directo, solo se inyecta un botón
        // flotante "Volver" antes de </body> en vez de envolverlo en otro layout.
        $backUrl = route('admin.captaciones.show', $captacion);
        $backButton = '<a href="' . e($backUrl) . '" style="position:fixed;top:16px;left:16px;z-index:9999;background:#1e293b;color:#fff;padding:.5rem 1rem;border-radius:8px;font-family:sans-serif;font-size:13px;text-decoration:none;box-shadow:0 4px 12px rgba(0,0,0,.25);">&larr; Volver a la captación</a>';
        $html = str_replace('</body>', $backButton . '</body>', $html);

        return response($html);
    }

    /** PDF Propuesta de Servicios — ver en el navegador */
    public function serviciosPdf(Captacion $captacion)
    {
        set_time_limit(120);
        $captacion->loadMissing(['client', 'property', 'createdBy']);

        if (empty($captacion->last_servicios_pdf_path) || !file_exists($captacion->last_servicios_pdf_path)) {
            app(ServiciosGeneratorService::class)->generatePdf($captacion);
        }

        $path = $captacion->last_servicios_pdf_path;

        return Response::make(file_get_contents($path), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="hdv-servicios.pdf"',
        ]);
    }

    /** PDF Propuesta de Servicios — forzar regeneración */
    public function serviciosRegenerate(Captacion $captacion)
    {
        try {
            app(ServiciosGeneratorService::class)->generatePdf($captacion);
        } catch (\Throwable $e) {
            return back()->with('error', 'Error al regenerar: ' . $e->getMessage());
        }

        return back()->with('success', 'Propuesta de servicios regenerada.');
    }

    /** Descargar PDF de Servicios */
    public function serviciosDownload(Captacion $captacion)
    {
        $captacion->loadMissing(['client']);

        if (empty($captacion->last_servicios_pdf_path) || !file_exists($captacion->last_servicios_pdf_path)) {
            app(ServiciosGeneratorService::class)->generatePdf($captacion);
        }

        $filename = 'HDV-Servicios-' . str_replace(' ', '-', $captacion->client->name ?? 'propietario') . '.pdf';

        return Response::download($captacion->last_servicios_pdf_path, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    /** Enviar Propuesta de Servicios por email */
    public function sendServiciosEmail(Request $request, Captacion $captacion)
    {
        $request->validate(['email' => 'required|email']);
        $captacion->loadMissing(['client', 'property', 'createdBy']);

        try {
            app(ServiciosGeneratorService::class)->sendByEmail(
                captacion: $captacion,
                email:     $request->input('email'),
                agent:     Auth::user(),
            );
        } catch (\Throwable $e) {
            return back()->with('error', 'Error al enviar email: ' . $e->getMessage());
        }

        return back()->with('success', 'Propuesta de servicios enviada a ' . $request->input('email'));
    }

    /** Enviar Propuesta de Servicios por WhatsApp */
    public function sendServiciosWhatsApp(Request $request, Captacion $captacion)
    {
        $request->validate(['phone' => 'required|string|max:30']);
        $captacion->loadMissing(['client', 'property', 'createdBy']);

        try {
            $result = app(ServiciosGeneratorService::class)->sendByWhatsApp(
                captacion: $captacion,
                phone:     $request->input('phone'),
                agent:     Auth::user(),
            );
        } catch (\Throwable $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }

        return redirect($result['wa_me_url']);
    }
}
