<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\FormSubmission;
use App\Models\Message;
use App\Models\SiteSetting;
use App\Services\EmailService;
use App\Services\LeadConversionService;
use Illuminate\Http\Request;

class FormSubmissionController extends Controller
{
    public function index(Request $request)
    {
        $query = FormSubmission::query()->latest();

        if ($type = $request->get('type')) {
            $query->where('form_type', $type);
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($tag = $request->get('tag')) {
            $query->where('lead_tag', $tag);
        }

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('email',    'like', "%{$search}%")
                  ->orWhere('phone',    'like', "%{$search}%");
            });
        }

        $submissions = $query->paginate(25)->withQueryString();

        $counts = [
            'total'     => FormSubmission::count(),
            'new'       => FormSubmission::where('status', 'new')->count(),
            'vendedor'  => FormSubmission::where('form_type', 'vendedor')->count(),
            'predio'    => FormSubmission::where('form_type', 'vendedor_predio')->count(),
            'comprador' => FormSubmission::where('form_type', 'comprador')->count(),
            'b2b'       => FormSubmission::where('form_type', 'b2b')->count(),
            'contacto'  => FormSubmission::where('form_type', 'contacto')->count(),
        ];

        return view('admin.form-submissions.index', compact('submissions', 'counts'));
    }

    public function create()
    {
        return view('admin.form-submissions.create');
    }

    /**
     * Alta manual de un lead — para leads que llegan por un canal que no
     * tiene integracion automatica (ej. Inmuebles24 antes de que el broker
     * confirme IMAP, o cualquier contacto directo). Usa la misma estructura
     * de payload que Inmuebles24LeadImporter (ref/codigo_aviso/etc. y
     * busca_tipo/busca_presupuesto/busca_zonas) para que la ficha del lead
     * (admin.form-submissions.show) renderice los mismos bloques "Aviso que
     * consultó" y "Lo que busca" sin importar si el lead vino del correo
     * automatico o se capturo a mano.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'form_type'        => 'required|string|max:50',
            'full_name'        => 'required|string|max:255',
            'email'            => 'required|email|max:255',
            'phone'            => 'nullable|string|max:50',
            'lead_tag'         => 'nullable|string|max:100',
            'client_type'      => 'nullable|string|in:buyer,renter,owner,investor',
            'lead_temperature' => 'nullable|string|in:hot,warm,cold',
            'budget_min'       => 'nullable|numeric|min:0',
            'budget_max'       => 'nullable|numeric|min:0',
            'property_type'    => 'nullable|string|max:255',
            'notes'            => 'nullable|string|max:2000',
            // Datos del aviso consultado (portales sin API, ej. Inmuebles24)
            'ref'               => 'nullable|string|max:100',
            'codigo_aviso'      => 'nullable|string|max:100',
            'codigo_anunciante' => 'nullable|string|max:100',
            'titulo_aviso'      => 'nullable|string|max:255',
            'tipo_operacion'    => 'nullable|string|max:50',
            'tipo_propiedad'    => 'nullable|string|max:100',
            'precio'            => 'nullable|string|max:100',
            'ubicacion'         => 'nullable|string|max:255',
            // Perfil de busqueda del interesado
            'busca_tipo'        => 'nullable|string|max:255',
            'busca_presupuesto' => 'nullable|string|max:100',
            'busca_zonas'       => 'nullable|string|max:500',
        ]);

        $payload = array_filter([
            'ref'               => $validated['ref'] ?? null,
            'codigo_aviso'      => $validated['codigo_aviso'] ?? null,
            'codigo_anunciante' => $validated['codigo_anunciante'] ?? null,
            'titulo_aviso'      => $validated['titulo_aviso'] ?? null,
            'tipo_operacion'    => $validated['tipo_operacion'] ?? null,
            'tipo_propiedad'    => $validated['tipo_propiedad'] ?? null,
            'precio'            => $validated['precio'] ?? null,
            'ubicacion'         => $validated['ubicacion'] ?? null,
            'busca_tipo'        => $validated['busca_tipo'] ?? null,
            'busca_presupuesto' => $validated['busca_presupuesto'] ?? null,
            'busca_zonas'       => !empty($validated['busca_zonas'])
                ? array_values(array_filter(array_map('trim', explode(',', $validated['busca_zonas']))))
                : null,
            'alta_manual'       => true,
        ], fn ($v) => $v !== null);

        // withoutEvents: alta manual = el broker ya leyo el lead antes de
        // capturarlo, no tiene sentido disparar el acuse automatico por
        // correo (mismo criterio ya usado para EasyBroker/Inmuebles24).
        $submission = FormSubmission::withoutEvents(fn () => FormSubmission::create([
            'form_type'        => $validated['form_type'],
            'source_page'      => $validated['form_type'] . ':manual',
            'full_name'        => $validated['full_name'],
            'email'            => $validated['email'],
            'phone'            => $validated['phone'] ?: 'sin teléfono',
            'lead_tag'         => $validated['lead_tag'] ?: null,
            'client_type'      => $validated['client_type'] ?? null,
            'lead_temperature' => $validated['lead_temperature'] ?? 'warm',
            'budget_min'       => $validated['budget_min'] ?? null,
            'budget_max'       => $validated['budget_max'] ?? null,
            'property_type'    => $validated['property_type'] ?? null,
            'status'           => 'new',
            'notes'            => $validated['notes'] ?? null,
            'payload'          => $payload,
        ]));

        return redirect()->route('admin.form-submissions.show', $submission)
            ->with('success', 'Lead creado manualmente.');
    }

    public function show(FormSubmission $formSubmission)
    {
        if (! $formSubmission->seen_at) {
            $formSubmission->update(['seen_at' => now()]);
        }
        $messages = $formSubmission->messages()->with('user')->latest()->get();
        $visits = $formSubmission->visits()->with(['property', 'user'])->get();

        // Inventario para el selector de "agendar visita" — si el lead ya
        // tiene una propiedad de interés detectada, va primero.
        $properties = \App\Models\Property::where('status', 'available')
            ->orderBy('address')
            ->select('id', 'address', 'colony')
            ->limit(200)
            ->get();
        $users = \App\Models\User::where('is_active', true)->orderBy('name')->select('id', 'name')->get();

        return view('admin.form-submissions.show', [
            'submission' => $formSubmission,
            'messages'   => $messages,
            'visits'     => $visits,
            'properties' => $properties,
            'users'      => $users,
        ]);
    }

    public function updateStatus(Request $request, FormSubmission $formSubmission)
    {
        $request->validate(['status' => 'required|in:new,contacted,qualified,won,lost']);
        $formSubmission->update([
            'status'       => $request->status,
            'contacted_at' => $request->status === 'contacted' && !$formSubmission->contacted_at ? now() : $formSubmission->contacted_at,
        ]);
        return back()->with('success', 'Estado actualizado');
    }

    public function updateNotes(Request $request, FormSubmission $formSubmission)
    {
        $request->validate(['notes' => 'nullable|string|max:2000']);
        $formSubmission->update(['notes' => $request->notes]);
        return back()->with('success', 'Notas guardadas');
    }

    public function convertToClient(FormSubmission $formSubmission, LeadConversionService $conversion)
    {
        // Leads de propietario (quiere vender/rentar su inmueble, o vender su
        // predio a una desarrolladora) van directo al wizard de captación con
        // el cliente ya cargado — evita re-teclear y duplicar. Otros tipos
        // (comprador, b2b, contacto) solo se convierten a Client, sin
        // captación. Ver docs/07-FLUJO-CAPTACION-Y-MEJORAS.md.
        $goesToCaptacion = in_array($formSubmission->form_type, ['vendedor', 'vendedor_predio']);

        if ($formSubmission->client_id && $goesToCaptacion) {
            return redirect()
                ->route('admin.captaciones.create-from-call', ['client_id' => $formSubmission->client_id, 'form_submission_id' => $formSubmission->id])
                ->with('success', 'Este lead ya tiene un cliente asociado.');
        }

        $result = $conversion->convert($formSubmission);
        $client = $result['client'];

        // La conversión ES el nacimiento del cliente (política de seguimiento): aquí se
        // disparan las automatizaciones de cliente nuevo — solo si de verdad es nuevo, no al
        // volver a convertir un lead que ya tenía cliente o se vinculó a uno existente.
        if (! $result['was_existing']) {
            try {
                app(\App\Services\AutomationEngine::class)->processNewClient($client);
            } catch (\Throwable $e) {
                \Log::warning('convertToClient: processNewClient falló', ['error' => $e->getMessage()]);
            }
        }

        $successMsg = $result['was_existing']
            ? "Lead vinculado al cliente existente «{$client->name}»."
            : "Cliente «{$client->name}» creado exitosamente.";
        if ($result['reparented_leads'] > 0 || $result['reparented_interactions'] > 0) {
            $successMsg .= ' Se recuperó su historial de ' . $result['reparented_leads'] . ' lead(s) y '
                . $result['reparented_interactions'] . ' interacción(es) previas (visitas, notas…).';
        }

        if ($goesToCaptacion) {
            return redirect()
                ->route('admin.captaciones.create-from-call', ['client_id' => $client->id, 'form_submission_id' => $formSubmission->id])
                ->with('success', $successMsg);
        }

        // Inquilino con una propiedad de interés detectada (de una visita, o del aviso de
        // Inmuebles24 que lo trajo) — directo al trato de renta prellenado, en vez de perderlo
        // en la lista de leads. Hallazgo real 2026-10-01 (Yarlin).
        if ($result['property_id'] && in_array('renta_inquilino', $client->interest_types ?? [], true)) {
            return redirect(route('rentals.create') . '?' . http_build_query([
                'property' => $result['property_id'],
                'owner'    => $result['owner_client_id'],
                'tenant'   => $client->id,
            ]))->with('success', $successMsg . ' Ya puedes crear el trato de renta del inmueble que le interesó.');
        }

        return back()->with('success', $successMsg);
    }

    /**
     * "Enviar checklist de requisitos" desde la ficha del lead — puramente
     * informativo, NO convierte a Client (decisión confirmada 2026-09-21:
     * la conversión y el acceso al portal quedan para cuando el broker
     * decida avanzar con ese prospecto e iniciar la investigación; mandar
     * el checklist a todos los interesados no debe comprometer nada).
     */
    public function sendTenantChecklist(FormSubmission $formSubmission)
    {
        if (!$formSubmission->email) {
            return back()->with('error', 'El lead necesita un email para mandarle el checklist.');
        }

        try {
            \Illuminate\Support\Facades\Mail::to($formSubmission->email)->send(
                new \App\Mail\V4\Mailables\TenantChecklistInvitationMail(nombre: $formSubmission->full_name)
            );
        } catch (\Exception $e) {
            \Log::warning('sendTenantChecklist (lead): envío falló', ['error' => $e->getMessage()]);
            return back()->with('error', 'Error al enviar el correo: ' . $e->getMessage());
        }

        return back()->with('success', "Checklist de requisitos enviado a {$formSubmission->email}.");
    }

    /**
     * Agendar visita para un lead que todavia no se convierte a Client — el
     * broker la agenda a mano (sin auto-agendado publico, decision
     * confirmada 2026-09-21). Espejo de ClientController::storeInteraction
     * para type=visit, pero contra VisitSchedulingService::createVisitForLead().
     */
    public function scheduleVisit(Request $request, FormSubmission $formSubmission)
    {
        $validated = $request->validate([
            // after_or_equal:today — hallazgo real 2026-10-01: Yarlin (lead ya visitó y dejó
            // feedback) recibió un correo de "tu visita está agendada" para una fecha ya pasada,
            // porque se reabrió este mismo formulario sobre el lead viejo. Ver
            // PropertyController::scheduleVisit.
            'scheduled_at_date'       => 'required|date|after_or_equal:today',
            'scheduled_at_time'       => 'required|date_format:H:i',
            'duracion'                => 'nullable|integer|in:30,60,90,120',
            'asesor_id'               => 'nullable|exists:users,id',
            'property_id'             => 'nullable|exists:properties,id',
            'description'             => 'nullable|string|max:1000',
            'send_confirmation_email' => 'nullable|boolean',
        ], [
            'scheduled_at_date.after_or_equal' => 'La fecha de la visita no puede ser anterior a hoy.',
        ]);

        $scheduledAt = \Carbon\Carbon::parse($validated['scheduled_at_date'] . ' ' . $validated['scheduled_at_time']);
        $property    = !empty($validated['property_id']) ? \App\Models\Property::find($validated['property_id']) : null;
        $asesorUser  = !empty($validated['asesor_id']) ? \App\Models\User::find($validated['asesor_id']) : null;

        app(\App\Services\VisitSchedulingService::class)->createVisitForLead(
            lead: $formSubmission,
            property: $property,
            broker: \Illuminate\Support\Facades\Auth::user(),
            scheduledAt: $scheduledAt,
            sendConfirmationEmail: $request->boolean('send_confirmation_email', true),
            description: $validated['description'] ?? null,
            asesorForEmail: $asesorUser,
            duracionMinutos: (string) ($validated['duracion'] ?? '30'),
        );

        return back()->with('success', 'Visita agendada. Se envió la confirmación a ' . $formSubmission->email . '.');
    }

    public function resendVisitConfirmation(FormSubmission $formSubmission, \App\Models\Interaction $interaction)
    {
        if (!$interaction->visit_token || !$formSubmission->email) {
            return back()->with('error', 'No se puede enviar la confirmación para esta visita.');
        }

        try {
            $scheduled = $interaction->scheduled_at;
            $prop      = $interaction->property;
            $asesor    = $interaction->user;

            $addressParts = array_filter([
                $prop?->address ?? '',
                $prop?->colony  ?? '',
                $prop?->city    ?? 'CDMX',
            ]);
            $mapsUrl = $addressParts
                ? 'https://www.google.com/maps/search/?api=1&query=' . urlencode(implode(', ', $addressParts))
                : '';

            \Illuminate\Support\Facades\Mail::to($formSubmission->email)->send(
                new \App\Mail\V4\Mailables\RecordatorioCitaMail(
                    new \App\Mail\V4\Data\RecordatorioCitaData(
                        email:        $formSubmission->email,
                        nombre:       $formSubmission->full_name,
                        dia_semana:   $scheduled?->locale('es')->dayName ?? '',
                        dia:          (string) ($scheduled?->day ?? ''),
                        mes:          $scheduled?->locale('es')->monthName ?? '',
                        anio:         (string) ($scheduled?->year ?? ''),
                        hora:         $scheduled?->format('g:i A') ?? '',
                        duracion:     (string) ($interaction->duracion ?? '30'),
                        direccion:    $prop?->address ?? 'A coordinar',
                        colonia:      $prop?->colony  ?? '',
                        asesor:       $asesor?->name  ?? '',
                        visit_token:  $interaction->visit_token,
                        maps_url:     $mapsUrl,
                        asesor_email: $asesor?->email ?? '',
                        asesor_phone: $asesor?->phone ?? $asesor?->whatsapp ?? '',
                    )
                )
            );
            $interaction->update(['reminder_sent_at' => now()]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('resendVisitConfirmation failed: ' . $e->getMessage());
            return back()->with('error', 'Error al enviar el correo: ' . $e->getMessage());
        }

        return back()->with('success', 'Recordatorio de confirmación enviado a ' . $formSubmission->email . '.');
    }

    public function sendVisitFeedbackRequest(FormSubmission $formSubmission, \App\Models\Interaction $interaction)
    {
        if (!$interaction->visit_token || !$formSubmission->email || $interaction->feedback_submitted_at) {
            return back()->with('error', 'No se puede solicitar feedback para esta visita.');
        }

        try {
            $interaction->loadMissing(['property.photos', 'user']);
            $addr = collect([$interaction->property?->address, $interaction->property?->colony])->filter()->implode(', ');

            \Illuminate\Support\Facades\Mail::to($formSubmission->email)->send(
                new \App\Mail\V4\Mailables\VisitFeedbackRequestMail($interaction, $formSubmission, $addr)
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('sendVisitFeedbackRequest failed: ' . $e->getMessage());
            return back()->with('error', 'Error al enviar el correo: ' . $e->getMessage());
        }

        return back()->with('success', 'Solicitud de opinión enviada a ' . $formSubmission->email . '.');
    }

    /**
     * Un "lead" de portal que en realidad es otro broker pidiendo colaboración
     * no se convierte en Client: se registra en Brokers Externos (el módulo de
     * comisión compartida) para la red de colaboración — de ahí salen los
     * envíos de inventario cuando hay que vender rápido.
     */
    public function convertToBroker(FormSubmission $formSubmission)
    {
        $verificacion = $formSubmission->payload['broker_verification_data'] ?? null;

        $existing = \App\Models\Broker::where(function ($q) use ($formSubmission, $verificacion) {
            $q->where('email', $formSubmission->email);
            if ($formSubmission->phone && $formSubmission->phone !== 'sin teléfono') {
                $q->orWhere('phone', $formSubmission->phone);
            }
            if (!empty($verificacion['phone'])) {
                $q->orWhere('phone', $verificacion['phone']);
            }
        })->first();

        if ($existing) {
            $formSubmission->update(['status' => 'qualified', 'notes' => trim(($formSubmission->notes ?? '') . "\nYa registrado en Brokers Externos (#{$existing->id}).")]);

            return redirect()->route('brokers.show', $existing)
                ->with('success', "Este contacto ya estaba en Brokers Externos: «{$existing->name}».");
        }

        // Especialidad inferida de la propiedad por la que preguntó
        $propiedad    = $formSubmission->payload['propiedad_local'] ?? null;
        $especialidad = $propiedad ? "Preguntó por: {$propiedad}" : null;

        $broker = \App\Models\Broker::create([
            'name'            => $verificacion['name'] ?? $formSubmission->full_name,
            'email'           => str_contains($formSubmission->email, '@sin-correo.easybroker') ? null : $formSubmission->email,
            'phone'           => $verificacion['phone'] ?? ($formSubmission->phone === 'sin teléfono' ? null : $formSubmission->phone),
            'company_name'    => $verificacion['company_name'] ?? null,
            'license_number'  => $verificacion['license_number'] ?? null,
            'interest_zones'      => $verificacion['interest_zones'] ?? null,
            'website'             => $verificacion['website'] ?? null,
            'operations_per_year' => $verificacion['operations_per_year'] ?? null,
            'birth_date'          => $verificacion['birth_date'] ?? null,
            'status'          => 'active',
            'specialty'       => $especialidad,
            'referral_source' => 'Lead de portal (' . ($formSubmission->payload['portal_origen'] ?? 'EasyBroker') . ')' . ($verificacion ? ' — verificado' : ''),
            'bio'             => $formSubmission->payload['mensaje'] ?? null,
        ]);

        $formSubmission->update(['status' => 'qualified', 'lead_tag' => 'LEAD_BROKER']);

        return redirect()->route('brokers.show', $broker)
            ->with('success', "«{$broker->name}» registrado en Brokers Externos" . ($verificacion ? ' con sus datos verificados.' : ' — completa su comisión y empresa.'));
    }

    public function rejectBroker(FormSubmission $formSubmission)
    {
        $payload = $formSubmission->payload ?? [];
        $payload['broker_verification_decision'] = 'rejected';
        $payload['broker_verification_decided_at'] = now()->toDateTimeString();

        $formSubmission->update([
            'payload' => $payload,
            'status'  => 'lost',
        ]);

        return back()->with('success', 'Contacto descartado — no se agregó a Brokers Externos.');
    }

    /**
     * Analiza el lead con IA bajo demanda: redacta la respuesta sugerida de
     * WhatsApp con todo el contexto (brief/propiedad) y, si es lead de portal
     * sin clasificar, lo clasifica de paso. Para cualquier tipo de lead.
     */
    public function aiSuggest(FormSubmission $formSubmission, \App\Services\AILeadClassifierService $classifier)
    {
        // Firma con el nombre de pila de quien está atendiendo (Alejandro,
        // Ana Laura…) — la respuesta la envía una persona, no "la empresa".
        $asesor = collect(explode(' ', trim((string) auth()->user()?->name)))->take(2)->implode(' ') ?: null;

        $respuesta = $classifier->suggestReply($formSubmission, $asesor);

        if ($respuesta === null) {
            return back()->with('error', 'La IA no respondió — intenta de nuevo en un momento.');
        }

        $payload = $formSubmission->payload ?? [];
        $payload['ai_respuesta'] = $respuesta;

        FormSubmission::withoutEvents(fn () => $formSubmission->update(['payload' => $payload]));

        return back()->with('success', 'Respuesta sugerida generada — revísala antes de enviar.');
    }

    /**
     * Envía la respuesta sugerida por IA (o la que esté en payload.ai_respuesta)
     * por correo al lead, dejando registro en Message (misma tabla que ya
     * alimenta la tarjeta "Mensajes enviados") para poder ver si se abrió.
     */
    public function sendEmail(FormSubmission $formSubmission, EmailService $emailService)
    {
        if (!$formSubmission->email) {
            return back()->with('error', 'Este lead no tiene correo electrónico registrado.');
        }

        $texto = $formSubmission->payload['ai_respuesta'] ?? null;
        if (!$texto) {
            return back()->with('error', 'Genera la respuesta sugerida primero.');
        }

        $user = auth()->user();
        $siteName = SiteSetting::first()?->site_name ?? 'Home del Valle';
        $subject = 'Respuesta a tu consulta — ' . $siteName;

        $bodyHtml = $this->buildLeadReplyEmailHtml($texto, $formSubmission, $user, $siteName);

        $msg = Message::create([
            'client_id' => $formSubmission->client_id,
            'user_id' => $user->id,
            'trackable_type' => FormSubmission::class,
            'trackable_id' => $formSubmission->id,
            'channel' => 'email',
            'direction' => 'outbound',
            'subject' => $subject,
            'body' => $texto,
            'status' => 'queued',
        ]);

        $sent = $emailService->send(
            $formSubmission->email,
            $subject,
            $bodyHtml,
            $formSubmission->full_name,
            null,
            $user,
            [],
            $msg->id
        );

        $msg->update(['status' => $sent ? 'sent' : 'failed', 'sent_at' => $sent ? now() : null]);

        if ($sent && !$formSubmission->contacted_at) {
            $formSubmission->update(['contacted_at' => now(), 'status' => $formSubmission->status === 'new' ? 'contacted' : $formSubmission->status]);
        }

        if (!$sent) {
            return back()->with('error', 'No se pudo enviar el correo. Verifica la configuración SMTP.');
        }

        return back()->with('success', 'Correo enviado a ' . $formSubmission->email . '.');
    }

    /**
     * El botón "Responder por WhatsApp" antes era un link plano a wa.me — nunca marcaba el lead
     * como contactado (hallazgo real 2026-10-02: con la mayoría del contacto real siendo por
     * WhatsApp, un lead podía llevar días de conversación y seguir viéndose "Nuevo" /
     * "Contactado: —" en el panel). Ahora pasa por aquí primero: marca contactado (mismo criterio
     * que sendEmail — solo la primera vez, nunca pisa un contacted_at ya puesto) y redirige a
     * wa.me con el mensaje contextual ya armado.
     */
    public function whatsappRedirect(FormSubmission $formSubmission)
    {
        if (!$formSubmission->phone || $formSubmission->phone === 'sin teléfono') {
            return back()->with('error', 'Este lead no tiene WhatsApp registrado.');
        }

        if (!$formSubmission->contacted_at) {
            $formSubmission->update([
                'contacted_at' => now(),
                'status' => $formSubmission->status === 'new' ? 'contacted' : $formSubmission->status,
            ]);
        }

        $phone = preg_replace('/[^0-9]/', '', $formSubmission->phone);
        $message = \App\Support\LeadWhatsAppMessage::build($formSubmission);

        return redirect('https://wa.me/' . $phone . '?text=' . urlencode($message));
    }

    private function buildLeadReplyEmailHtml(string $message, FormSubmission $formSubmission, $user, string $siteName): string
    {
        $senderName = $user->full_name ?? $user->name;
        $senderTitle = $user->title ?? 'Asesor Inmobiliario';
        $senderPhone = $user->phone ?? '';
        $senderEmail = $user->mailSetting?->from_email ?? $user->email;

        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>
            body { font-family: Arial, Helvetica, sans-serif; color: #333; margin: 0; padding: 0; background: #f4f4f4; }
            .email-wrap { max-width: 640px; margin: 0 auto; background: #fff; }
            .email-header { background: linear-gradient(135deg, #667eea, #764ba2); padding: 24px 32px; color: #fff; }
            .email-header h1 { margin: 0; font-size: 20px; }
            .email-body { padding: 32px; }
            .email-body p { line-height: 1.6; margin: 0 0 16px; }
            .email-footer { background: #f8fafc; padding: 24px 32px; border-top: 1px solid #e2e8f0; font-size: 13px; color: #64748b; }
            .sig-name { font-weight: 600; color: #1e293b; font-size: 14px; }
        </style></head><body>';
        $html .= '<div class="email-wrap">';
        $html .= '<div class="email-header"><h1>' . e($siteName) . '</h1></div>';
        $html .= '<div class="email-body">';
        $html .= '<p>Hola <strong>' . e($formSubmission->full_name) . '</strong>,</p>';
        $html .= '<p>' . nl2br(e($message)) . '</p>';
        $html .= '<div style="margin-top:32px; padding-top:16px; border-top:1px solid #e2e8f0;">';
        if ($user->email_signature) {
            $html .= $user->email_signature;
        } else {
            $html .= '<p class="sig-name">' . e($senderName) . '</p>';
            $html .= '<p style="margin:0; font-size:13px; color:#64748b;">' . e($senderTitle) . '</p>';
            if ($senderPhone) {
                $html .= '<p style="margin:2px 0 0; font-size:13px; color:#64748b;">' . e($senderPhone) . '</p>';
            }
            $html .= '<p style="margin:2px 0 0; font-size:13px; color:#667eea;">' . e($senderEmail) . '</p>';
        }
        $html .= '</div></div>';
        $html .= '<div class="email-footer"><p style="margin:0;">' . e($siteName) . ' &middot; Tu plataforma de gestión inmobiliaria</p></div>';
        $html .= '</div></body></html>';

        return $html;
    }

    public function destroy(FormSubmission $formSubmission)
    {
        try {
            $formSubmission->delete();
        } catch (\Throwable) {
            // Si falla el cleanup de media, forzar borrado directo
            \DB::table('form_submissions')->where('id', $formSubmission->id)->delete();
        }
        return redirect()->route('admin.form-submissions.index')->with('success', 'Lead eliminado');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'integer']);
        // Borrado directo sin disparar eventos de Media Library
        $count = \DB::table('form_submissions')->whereIn('id', $request->ids)->delete();
        return redirect()->route('admin.form-submissions.index')->with('success', "{$count} leads eliminados");
    }
}
