<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Broker;
use App\Models\Interaction;
use App\Models\Notification;
use App\Models\Property;
use App\Models\User;
use App\Models\MarketingChannel;
use App\Models\MarketingCampaign;
use App\Helpers\MentionHelper;
use App\Services\ClientPortalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Client::with(['broker', 'marketingChannel', 'assignedUser']);

        // Scope to own leads if user only has leads.view.own
        if (!$user->hasPermission('leads.view') && $user->hasPermission('leads.view.own')) {
            $query->where('assigned_user_id', $user->id);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        if ($type = $request->input('property_type')) {
            $query->where('property_type', $type);
        }
        if ($brokerId = $request->input('broker_id')) {
            $query->where('broker_id', $brokerId);
        }
        if ($channelId = $request->input('marketing_channel_id')) {
            $query->where('marketing_channel_id', $channelId);
        }
        if ($temperature = $request->input('lead_temperature')) {
            $query->where('lead_temperature', $temperature);
        }
        if ($priority = $request->input('priority')) {
            $query->where('priority', $priority);
        }
        if ($request->filled('budget_min')) {
            $query->where('budget_max', '>=', $request->input('budget_min'));
        }
        if ($request->filled('budget_max')) {
            $query->where('budget_min', '<=', $request->input('budget_max'));
        }

        $clients = $query->latest()->paginate(12)->withQueryString();
        $brokers = Broker::where('status', 'active')->orderBy('name')->get();
        $channels = MarketingChannel::active()->ordered()->get();
        return view('clients.index', compact('clients', 'brokers', 'channels'));
    }

    public function create()
    {
        $this->authorize('create', Client::class);
        $brokers = Broker::all();
        $channels = MarketingChannel::active()->ordered()->get();
        $campaigns = MarketingCampaign::active()->get();
        return view('clients.create', compact('brokers', 'channels', 'campaigns'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Client::class);

        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:clients',
            'phone' => 'nullable|string|max:20',
            'whatsapp' => 'nullable|string|max:20',
            'city'   => 'nullable|string',
            'curp'   => 'nullable|string|max:18',
            'rfc'    => 'nullable|string|max:13',
            'interest_types'   => 'nullable|array',
            'interest_types.*' => 'in:compra,venta,renta_propietario,renta_inquilino',
            'lead_temperature' => 'nullable|in:frio,tibio,caliente',
            'priority'         => 'nullable|in:alta,media,baja',
            'initial_notes'    => 'nullable|string|max:2000',
            'budget_min'       => 'nullable|numeric',
            'budget_max'       => 'nullable|numeric',
            'property_type'    => 'nullable|string',
            'search_urgency'   => 'nullable|in:inmediata,1_3_meses,3_6_meses,sin_prisa',
            'broker_id'        => 'nullable|exists:brokers,id',
            'photo'            => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'marketing_channel_id'  => 'nullable|exists:marketing_channels,id',
            'marketing_campaign_id' => 'nullable|exists:marketing_campaigns,id',
            'acquisition_cost' => 'nullable|numeric|min:0',
            // Ingresos y financiamiento
            'income_type'       => 'nullable|in:nomina,independiente,negocio_propio,pension,otro',
            'income_amount'     => 'nullable|numeric|min:0',
            'financing_type'    => 'nullable|in:contado,credito_bancario,infonavit,fovissste,mixto',
            'financing_preauth_amount' => 'nullable|numeric|min:0',
            'nss'               => 'nullable|string|max:20',
            'infonavit_balance' => 'nullable|numeric|min:0',
            // Datos legales
            'first_name'        => 'nullable|string|max:100',
            'last_name_paterno' => 'nullable|string|max:100',
            'last_name_materno' => 'nullable|string|max:100',
            'birth_date'        => 'nullable|date',
            'birth_state'       => 'nullable|string|max:50',
            'gender'            => 'nullable|in:H,M',
            'nationality'       => 'nullable|string|max:50',
            'marital_status'    => 'nullable|in:soltero,casado,divorciado,viudo,union_libre',
            'occupation'        => 'nullable|string|max:120',
            'id_type'           => 'nullable|in:INE,pasaporte,cedula_profesional,otro',
            'id_number'         => 'nullable|string|max:60',
            'id_expiry'         => 'nullable|date',
            'address_street'       => 'nullable|string|max:200',
            'address_colony'       => 'nullable|string|max:100',
            'address_municipality' => 'nullable|string|max:100',
            'address_state'        => 'nullable|string|max:60',
            'address_zip'          => 'nullable|string|max:5',
            'marital_regime'    => 'nullable|in:separacion_bienes,sociedad_conyugal',
            'spouse_name'       => 'nullable|string|max:200',
            'spouse_curp'       => 'nullable|string|max:18',
            'bank_clabe'        => 'nullable|string|max:18',
            'bank_name'         => 'nullable|string|max:80',
        ]);

        $validated['assigned_user_id'] = Auth::id();
        $validated['lead_source'] = 'manual';

        $validated['client_type'] = Client::deriveClientType($validated['interest_types'] ?? [], $request->boolean('is_investor'));

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('clients', 'public');
        }

        Client::create($validated);

        // Trigger new_client automations
        $newClient = Client::where('email', $validated['email'])->first();
        if ($newClient) {
            app(\App\Services\AutomationEngine::class)->processNewClient($newClient);
            \App\Models\LeadEvent::record($newClient->id, 'new_client_created', ['source' => 'manual']);
        }

        return redirect()->route('clients.index')->with('success', 'Cliente creado exitosamente');
    }

    public function show(string $id)
    {
        $client = Client::with(['broker', 'marketingChannel', 'marketingCampaign', 'ownedProperties'])->findOrFail($id);
        $this->authorize('view', $client);

        $emails = $client->emails()->with('user')->latest()->get();
        $interactions = $client->interactions()->with(['user', 'valuation'])->latest()->get();

        // Properties where this client is the owner
        $ownedProperties = $client->ownedProperties()->with('broker')->get();

        // Properties linked via deals (interested)
        $dealProperties = Property::whereHas('deals', fn($q) => $q->where('client_id', $client->id))
            ->with('broker')
            ->get();

        // Full inventory for visit scheduling (exclude client's own and deal props to avoid dupes)
        $excludedIds = $ownedProperties->pluck('id')->merge($dealProperties->pluck('id'))->unique();
        $allActiveProperties = Property::where('status', 'available')
            ->whereNotIn('id', $excludedIds)
            ->orderBy('address')
            ->select('id', 'address', 'colony')
            ->limit(200)
            ->get();

        // Properties sent via email
        $emailPropertyIds = $emails->pluck('property_ids')->filter()->flatten()->unique()->values();
        $emailProperties = $emailPropertyIds->isNotEmpty()
            ? Property::whereIn('id', $emailPropertyIds)->get()
            : collect();

        $emailsSent = $emails->where('status', 'sent')->count();
        $emailsOpened = $emails->whereNotNull('opened_at')->count();

        // Build unified timeline
        $timeline = collect();

        foreach ($emails as $email) {
            $statusHtml = match ($email->status) {
                'failed' => '<span class="email-status status-failed">&#10007; Fallido</span>',
                default => $email->is_opened
                    ? '<span class="email-status status-opened">&#10003; Abierto ' . $email->open_count . 'x</span>'
                    : '<span class="email-status status-sent">Enviado</span>',
            };

            $propsHtml = '';
            if ($email->property_ids && count($email->property_ids) > 0) {
                $props = $email->properties();
                $propsHtml = '<div class="props-sent">' . $props->map(fn($p) => '<span class="prop-tag">' . e($p->title) . '</span>')->join('') . '</div>';
            }

            $timeline->push([
                'date' => $email->created_at,
                'dot' => 'email',
                'color' => '#3b82f6',
                'type_label' => 'Correo',
                'body' => '<strong>' . e($email->subject) . '</strong> ' . $statusHtml . $propsHtml,
                'meta' => 'Por ' . e($email->user->name ?? 'Sistema') . ' &middot; <a href="' . route('clients.email.show', $email) . '" style="color:var(--primary);">Ver correo</a>',
            ]);
        }

        $typeConfig = [
            'note'                  => ['dot' => 'note',         'color' => '#8b5cf6', 'label' => 'Nota'],
            'call'                  => ['dot' => 'call',         'color' => '#10b981', 'label' => 'Llamada'],
            'visit'                 => ['dot' => 'visit',        'color' => '#f59e0b', 'label' => 'Visita'],
            'meeting'               => ['dot' => 'meeting',      'color' => '#ef4444', 'label' => 'Reunión'],
            'whatsapp'              => ['dot' => 'whatsapp',     'color' => '#25d366', 'label' => 'WhatsApp'],
            'email'                 => ['dot' => 'email',        'color' => '#3b82f6', 'label' => 'Correo'],
            'presentation_email'    => ['dot' => 'presentation', 'color' => '#667eea', 'label' => 'Presentación Email'],
            'presentation_whatsapp' => ['dot' => 'presentation', 'color' => '#667eea', 'label' => 'Presentación WhatsApp'],
            'valuation'             => ['dot' => 'valuation',    'color' => '#0c1a2e', 'label' => 'Opinión de Valor'],
        ];

        foreach ($interactions as $interaction) {
            $config = $typeConfig[$interaction->type] ?? ['dot' => 'system', 'color' => '#94a3b8', 'label' => ucfirst($interaction->type)];

            $bodyHtml = MentionHelper::render($interaction->description);

            // Si la interacción tiene valuación vinculada, agregar link
            if ($interaction->valuation_id) {
                $bodyHtml .= ' <a href="' . route('admin.valuations.show', $interaction->valuation_id) . '" style="color:var(--primary);font-size:.78rem;">Ver valuación →</a>';
            }

            // Enriquecer visitas con todos sus datos relevantes
            if ($interaction->type === 'visit') {
                // Fecha y hora agendada
                if ($interaction->scheduled_at) {
                    $bodyHtml .= '<div style="margin-top:5px;font-size:.82rem;color:#5a6573;">📅 '
                        . e($interaction->scheduled_at->format('d/m/Y · H:i')) . ' h'
                        . ($interaction->duracion ? ' · ' . $interaction->duracion . ' min' : '')
                        . '</div>';
                }
                // Inmueble
                if ($interaction->property_id && $interaction->property) {
                    $bodyHtml .= '<div style="font-size:.82rem;color:#5a6573;margin-top:2px;">🏠 '
                        . e($interaction->property->address ?? 'Inmueble vinculado')
                        . ($interaction->property->colony ? ', ' . e($interaction->property->colony) : '')
                        . '</div>';
                }
                // Badge de estado
                if ($interaction->confirmed_at) {
                    $bodyHtml .= '<div style="margin-top:6px;"><span style="display:inline-block;background:#d1fae5;color:#065f46;font-size:.75rem;font-weight:700;padding:2px 8px;border-radius:99px;">✓ Visita confirmada · ' . e($interaction->confirmed_at->format('d/m H:i')) . '</span></div>';
                } elseif ($interaction->reschedule_requested_at) {
                    $bodyHtml .= '<div style="margin-top:6px;"><span style="display:inline-block;background:#fef3c7;color:#92400e;font-size:.75rem;font-weight:700;padding:2px 8px;border-radius:99px;">↩ Solicitud de reagendamiento · ' . e($interaction->reschedule_requested_at->format('d/m H:i')) . '</span></div>';
                    if ($interaction->reschedule_message) {
                        $bodyHtml .= '<div style="margin-top:4px;font-size:.82rem;color:#5a6573;background:#fffbeb;padding:6px 10px;border-radius:6px;border-left:3px solid #f59e0b;">'
                            . '"' . e($interaction->reschedule_message) . '"'
                            . '</div>';
                    }
                } elseif ($interaction->scheduled_at && $interaction->scheduled_at->isFuture()) {
                    $bodyHtml .= '<div style="margin-top:6px;"><span style="display:inline-block;background:#eff6ff;color:#1d4ed8;font-size:.75rem;font-weight:700;padding:2px 8px;border-radius:99px;">⏳ Pendiente de confirmar</span></div>';
                }
            }

            // Botones de acción para visitas
            $actionsHtml = '';
            if ($interaction->type === 'visit' && $interaction->visit_token) {

                // ── Paso 1: Confirmación de asistencia ──────────────────────
                // Mostrar siempre que no esté confirmada (cita futura o pasada)
                if (!$interaction->confirmed_at) {
                    $confirmUrl = route('clients.interaction.send-confirmation', [$client->id, $interaction->id]);
                    if ($interaction->reminder_sent_at) {
                        // Ya se envió: mostrar badge + opción de reenviar
                        $actionsHtml .= '<span style="background:#eff6ff;border:1px solid #bfdbfe;color:#1d4ed8;border-radius:6px;padding:4px 9px;font-size:.75rem;font-weight:600;display:inline-flex;align-items:center;gap:4px;margin-top:8px;margin-right:6px;">✉️ Confirmación enviada</span>';
                        $actionsHtml .= '<form method="POST" action="' . $confirmUrl . '" style="display:inline-block;margin-top:8px;margin-right:6px;">'
                            . csrf_field()
                            . '<button type="submit" style="background:#fff;border:1px solid #bfdbfe;color:#1d4ed8;border-radius:6px;padding:4px 10px;font-size:.75rem;font-weight:600;cursor:pointer;font-family:inherit;">↩ Reenviar</button>'
                            . '</form>';
                    } else {
                        // Aún no enviada
                        $actionsHtml .= '<form method="POST" action="' . $confirmUrl . '" style="display:inline-block;margin-top:8px;margin-right:6px;">'
                            . csrf_field()
                            . '<button type="submit" style="background:#eff6ff;border:1px solid #bfdbfe;color:#1d4ed8;border-radius:6px;padding:4px 10px;font-size:.75rem;font-weight:600;cursor:pointer;font-family:inherit;">📤 Enviar confirmación</button>'
                            . '</form>';
                    }
                } else {
                    // Confirmada: mostrar badge verde
                    $actionsHtml .= '<span style="background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;border-radius:6px;padding:4px 9px;font-size:.75rem;font-weight:600;display:inline-flex;align-items:center;gap:4px;margin-top:8px;margin-right:6px;">✅ Asistencia confirmada</span>';
                }

                // ── Paso 2: Solicitar opinión (visita confirmada, o ya pasó la fecha —
                // no depende de haber reenviado el recordatorio manualmente: el camino
                // normal es que el cliente confirme directo desde el correo automático
                // original y nunca se toque "Enviar confirmación") ──
                if ($interaction->confirmed_at || $interaction->scheduled_at?->isPast()) {
                    if ($interaction->feedback_submitted_at) {
                        // Mostrar resumen del feedback recibido
                        $reactionEmoji = match($interaction->visitor_reaction) {
                            'liked'    => '👍',
                            'neutral'  => '🤔',
                            'disliked' => '❌',
                            default    => '💬',
                        };
                        $reactionLabel = match($interaction->visitor_reaction) {
                            'liked'    => 'Le gustó',
                            'neutral'  => 'Tiene dudas',
                            'disliked' => 'No cumplió',
                            default    => 'Opinó',
                        };
                        $priceLabel = match($interaction->price_perception) {
                            'fair'       => '✅ Precio justo',
                            'negotiable' => '💬 Precio negociable',
                            'high'       => '💸 Precio alto',
                            default      => null,
                        };
                        $stars = $interaction->advisor_rating
                            ? str_repeat('★', $interaction->advisor_rating) . str_repeat('☆', 5 - $interaction->advisor_rating)
                            : null;

                        $actionsHtml .= '<div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:8px;">';
                        $actionsHtml .= '<span style="background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;border-radius:6px;padding:3px 9px;font-size:.75rem;font-weight:700;">'
                            . $reactionEmoji . ' ' . e($reactionLabel) . '</span>';
                        if ($priceLabel) {
                            $priceColor = $interaction->price_perception === 'fair'
                                ? 'background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;'
                                : ($interaction->price_perception === 'high'
                                    ? 'background:#fef2f2;border:1px solid #fecaca;color:#991b1b;'
                                    : 'background:#fffbeb;border:1px solid #fde68a;color:#92400e;');
                            $actionsHtml .= '<span style="' . $priceColor . 'border-radius:6px;padding:3px 9px;font-size:.75rem;font-weight:700;">' . e($priceLabel) . '</span>';
                        }
                        if ($stars) {
                            $actionsHtml .= '<span style="background:#fefce8;border:1px solid #fde68a;color:#92400e;border-radius:6px;padding:3px 9px;font-size:.75rem;font-weight:700;" title="Calificación del asesor">'
                                . e($stars) . '</span>';
                        }
                        $actionsHtml .= '</div>';
                    } else {
                        $feedbackUrl = route('clients.interaction.send-feedback', [$client->id, $interaction->id]);
                        $actionsHtml .= '<form method="POST" action="' . $feedbackUrl . '" style="display:inline-block;margin-top:8px;">'
                            . csrf_field()
                            . '<button type="submit" style="background:#f5f3ff;border:1px solid #ddd6fe;color:#7c3aed;border-radius:6px;padding:5px 12px;font-size:.75rem;font-weight:600;cursor:pointer;font-family:inherit;">💬 Solicitar opinión</button>'
                            . '</form>';
                    }
                }
            }

            $timeline->push([
                'date'       => $interaction->created_at,
                'dot'        => $config['dot'],
                'color'      => $config['color'],
                'type_label' => $config['label'],
                'body'       => $bodyHtml,
                'meta'       => 'Por ' . e($interaction->user->name ?? ''),
                'actions'    => $actionsHtml,
            ]);
        }

        // Presentación sends → también al timeline
        try {
            $presentationSends = \App\Models\PresentationSend::whereHas(
                'captacion', fn($q) => $q->where('client_id', $client->id)
            )->with(['captacion', 'sentBy'])->latest('sent_at')->get();

            foreach ($presentationSends as $ps) {
                $trackingBadges = '';
                if ($ps->email_opened_at)    $trackingBadges .= '<span style="background:#ecfdf5;color:#065f46;padding:1px 6px;border-radius:3px;font-size:.7rem;font-weight:600;margin-left:4px;">✓ Abierto</span>';
                if ($ps->pdf_viewed_at)      $trackingBadges .= '<span style="background:#ecfdf5;color:#065f46;padding:1px 6px;border-radius:3px;font-size:.7rem;font-weight:600;margin-left:4px;">✓ PDF visto ×' . $ps->pdf_view_count . '</span>';
                if ($ps->pdf_downloaded_at)  $trackingBadges .= '<span style="background:#ecfdf5;color:#065f46;padding:1px 6px;border-radius:3px;font-size:.7rem;font-weight:600;margin-left:4px;">✓ Descargado</span>';

                $channelLabel = $ps->channel === 'email' ? 'por email' : 'por WhatsApp';
                $addressDisplay = $ps->captacion?->property_address_display ?? '';

                $timeline->push([
                    'date'       => $ps->sent_at,
                    'dot'        => 'presentation',
                    'color'      => '#667eea',
                    'type_label' => 'Presentación',
                    'body'       => 'Presentación inicial enviada ' . $channelLabel
                                    . ($addressDisplay ? ' · <em>' . e($addressDisplay) . '</em>' : '')
                                    . $trackingBadges,
                    'meta'       => 'Por ' . e($ps->sentBy->name ?? 'Sistema')
                                    . ' &middot; <a href="' . route('admin.captaciones.presentation', $ps->captacion_id) . '" style="color:var(--primary);">Ver presentación</a>',
                ]);
            }
        } catch (\Throwable $e) {
            // silencioso si la tabla aún no existe
        }

        $timeline = $timeline->sortByDesc('date')->values();

        $confidencialidadRequest = \App\Models\GoogleSignatureRequest::where('contacto_id', $client->id)
            ->where('tipo', 'confidencialidad')
            ->latest()
            ->first();

        // Documents: direct + captacion + rental
        $rentalIds = \App\Models\RentalProcess::where('owner_client_id', $client->id)
            ->orWhere('tenant_client_id', $client->id)
            ->pluck('id');

        try {
            // Sin filtrar status: una captación completada sigue mostrando sus
            // documentos aquí (mismo bug ya corregido en el Portal — ver
            // memoria del proyecto).
            $captacion = \App\Models\Captacion::where('client_id', $client->id)
                ->with('documents.uploader')
                ->latest()
                ->first();
        } catch (\Throwable $e) {
            $captacion = null;
        }

        try {
            $hasCaptacionCol = \Illuminate\Support\Facades\Schema::hasColumn('documents', 'captacion_id');
            $clientDocsQuery = \App\Models\Document::where('client_id', $client->id);
            if ($hasCaptacionCol) {
                // Excluir docs de captación excepto presentaciones PDF y opiniones de valor
                $clientDocsQuery->where(function ($q) {
                    $q->whereNull('captacion_id')
                      ->orWhereIn('category', ['presentation_pdf', 'opinion_valor']);
                });
            }
            $clientDocs = $clientDocsQuery->with(['uploader', 'captacion', 'valuation', 'property'])->latest()->get();
        } catch (\Throwable $e) {
            $clientDocs = collect();
        }

        $allDocCategories = \App\Models\Document::CATEGORIES;

        $users = User::where('is_active', true)->orderBy('name')->select('id', 'name', 'email', 'phone')->get();

        return view('clients.show', compact(
            'client', 'emails', 'interactions', 'emailsSent', 'emailsOpened',
            'timeline', 'ownedProperties', 'dealProperties', 'emailProperties',
            'confidencialidadRequest', 'captacion', 'clientDocs', 'allDocCategories',
            'allActiveProperties', 'users'
        ));
    }

    public function edit(string $id)
    {
        $client = Client::findOrFail($id);
        $this->authorize('update', $client);
        $brokers = Broker::all();
        $channels = MarketingChannel::active()->ordered()->get();
        $campaigns = MarketingCampaign::active()->get();
        return view('clients.edit', compact('client', 'brokers', 'channels', 'campaigns'));
    }

    public function update(Request $request, string $id)
    {
        $client = Client::findOrFail($id);
        $this->authorize('update', $client);

        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:clients,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'whatsapp' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city'   => 'nullable|string',
            'curp'   => 'nullable|string|max:18',
            'rfc'    => 'nullable|string|max:13',
            'interest_types'   => 'nullable|array',
            'interest_types.*' => 'in:compra,venta,renta_propietario,renta_inquilino',
            'lead_temperature' => 'nullable|in:frio,tibio,caliente',
            'priority'         => 'nullable|in:alta,media,baja',
            'initial_notes'    => 'nullable|string|max:2000',
            'budget_min'       => 'nullable|numeric',
            'budget_max'       => 'nullable|numeric',
            'property_type'    => 'nullable|string',
            'broker_id'        => 'nullable|exists:brokers,id',
            'photo'            => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'marketing_channel_id'  => 'nullable|exists:marketing_channels,id',
            'marketing_campaign_id' => 'nullable|exists:marketing_campaigns,id',
            'acquisition_cost' => 'nullable|numeric|min:0',
            // Datos legales
            'first_name'        => 'nullable|string|max:100',
            'last_name_paterno' => 'nullable|string|max:100',
            'last_name_materno' => 'nullable|string|max:100',
            'birth_date'        => 'nullable|date',
            'birth_state'       => 'nullable|string|max:50',
            'gender'            => 'nullable|in:H,M',
            'nationality'       => 'nullable|string|max:50',
            'marital_status'    => 'nullable|in:soltero,casado,divorciado,viudo,union_libre',
            'occupation'        => 'nullable|string|max:120',
            'id_type'           => 'nullable|in:INE,pasaporte,cedula_profesional,otro',
            'id_number'         => 'nullable|string|max:60',
            'id_expiry'         => 'nullable|date',
            'address_street'       => 'nullable|string|max:200',
            'address_colony'       => 'nullable|string|max:100',
            'address_municipality' => 'nullable|string|max:100',
            'address_state'        => 'nullable|string|max:60',
            'address_zip'          => 'nullable|string|max:5',
            'marital_regime'    => 'nullable|in:separacion_bienes,sociedad_conyugal',
            'spouse_name'       => 'nullable|string|max:200',
            'spouse_curp'       => 'nullable|string|max:18',
            'bank_clabe'        => 'nullable|string|max:18',
            'bank_name'         => 'nullable|string|max:80',
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('clients', 'public');
        }

        // Ensure interest_types is cleared when no checkboxes are selected
        if (!$request->has('interest_types')) {
            $validated['interest_types'] = [];
        }

        // Re-derivar client_type al editar interest_types — antes quedaba
        // desactualizado (bug real, auditoría 2026-07-04). El form de edición
        // no tiene checkbox "es inversionista" (a diferencia de create), así
        // que se preserva ese estado si ya lo tenía.
        $derivedClientType = Client::deriveClientType($validated['interest_types'] ?? [], $client->client_type === 'investor');
        if ($derivedClientType) {
            $validated['client_type'] = $derivedClientType;
        }

        $client->update($validated);
        return redirect()->route('clients.edit', $client)->with('success', 'Cliente actualizado exitosamente');
    }

    public function destroy(string $id)
    {
        $client = Client::findOrFail($id);
        $this->authorize('delete', $client);

        // Eliminar usuario del portal vinculado
        if ($client->user_id) {
            \App\Models\User::where('id', $client->user_id)->delete();
        }

        $client->delete();
        return redirect()->route('clients.index')->with('success', 'Cliente eliminado exitosamente');
    }

    /**
     * Store a quick interaction/note for a client.
     */
    public function storeInteraction(Request $request, Client $client)
    {
        $validated = $request->validate([
            'type'                   => 'required|in:note,call,visit,meeting,whatsapp',
            'description'            => 'required|string|max:1000',
            'scheduled_at_date'      => 'required_if:type,visit|nullable|date',
            'scheduled_at_time'      => 'required_if:type,visit|nullable|date_format:H:i',
            'duracion'               => 'nullable|integer|in:30,60,90,120',
            'asesor_id'              => 'nullable|exists:users,id',
            'property_id'            => 'nullable|exists:properties,id',
            'send_confirmation_email'=> 'nullable|boolean',
        ]);

        // Resolve asesor user data
        $asesorUser = !empty($validated['asesor_id']) ? User::find($validated['asesor_id']) : null;
        $asesorNombre = $asesorUser?->name ?? auth()->user()->name ?? 'Tu asesor';
        $asesorEmail  = $asesorUser?->email ?? '';
        $asesorPhone  = $asesorUser?->phone ?? '';

        if ($validated['type'] === 'visit' && !empty($validated['scheduled_at_date'])) {
            $time = $validated['scheduled_at_time'] ?? '10:00';
            $scheduledAt = \Carbon\Carbon::parse($validated['scheduled_at_date'] . ' ' . $time);
            $property = !empty($validated['property_id']) ? Property::find($validated['property_id']) : null;

            $interaction = app(\App\Services\VisitSchedulingService::class)->createVisit(
                client: $client,
                property: $property,
                broker: Auth::user(),
                scheduledAt: $scheduledAt,
                sendConfirmationEmail: $request->boolean('send_confirmation_email', true),
                description: $validated['description'],
                asesorForEmail: $asesorUser,
                duracionMinutos: (string) ($validated['duracion'] ?? '30'),
            );
        } else {
            $interactionData = [
                'client_id'    => $client->id,
                'user_id'      => Auth::id(),
                'type'         => $validated['type'],
                'description'  => $validated['description'],
                'completed_at' => now(),
            ];

            if (!empty($validated['property_id'])) {
                $interactionData['property_id'] = $validated['property_id'];
            }

            $interaction = Interaction::create($interactionData);

            $eventMap = ['call' => 'call_completed', 'visit' => 'visit_completed', 'meeting' => 'visit_completed', 'whatsapp' => 'message_sent'];
            $scoringEvent = $eventMap[$validated['type']] ?? null;
            if ($scoringEvent) {
                app(\App\Services\LeadScoringService::class)->processEvent($client->id, $scoringEvent, ['source' => 'interaction']);
            }
        }

        // Parse @mentions and create notifications
        $this->processMentions($validated['description'], $interaction, $client);

        return redirect()->route('clients.show', $client)->with('success', 'Nota agregada.');
    }

    public function resendConfirmation(Client $client, Interaction $interaction)
    {
        if (!$interaction->visit_token || !$client->email) {
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

            \Illuminate\Support\Facades\Mail::to($client->email)->send(
                new \App\Mail\V4\Mailables\RecordatorioCitaMail(
                    new \App\Mail\V4\Data\RecordatorioCitaData(
                        email:        $client->email,
                        nombre:       $client->name,
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
            \Illuminate\Support\Facades\Log::warning('resendConfirmation failed: ' . $e->getMessage());
            return back()->with('error', 'Error al enviar el correo: ' . $e->getMessage());
        }

        return back()->with('success', 'Recordatorio de confirmación enviado a ' . $client->email . '.');
    }

    public function sendFeedbackRequest(Client $client, Interaction $interaction)
    {
        if (!$interaction->visit_token || !$client->email || $interaction->feedback_submitted_at) {
            return back()->with('error', 'No se puede solicitar feedback para esta visita.');
        }

        try {
            $interaction->loadMissing(['property.photos', 'user']);
            $prop      = $interaction->property;
            $scheduled = $interaction->scheduled_at;
            $addr      = collect([$prop?->address, $prop?->colony])->filter()->implode(', ');

            \Illuminate\Support\Facades\Mail::to($client->email)->send(
                new \App\Mail\V4\Mailables\VisitFeedbackRequestMail($interaction, $client, $addr)
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('sendFeedbackRequest failed: ' . $e->getMessage());
            return back()->with('error', 'Error al enviar el correo: ' . $e->getMessage());
        }

        return back()->with('success', 'Solicitud de opinión enviada a ' . $client->email . '.');
    }

    /**
     * Parse @mentions from text and notify mentioned users.
     */
    private function processMentions(string $text, Interaction $interaction, Client $client): void
    {
        // Match @Name Lastname or @Name patterns
        preg_match_all('/@([A-Za-zÀ-ÿ]+(?:\s+[A-Za-zÀ-ÿ]+)?)/', $text, $matches);

        if (empty($matches[1])) {
            return;
        }

        $currentUserId = Auth::id();
        $currentUser = Auth::user();
        $notified = [];

        foreach ($matches[1] as $mention) {
            $mention = trim($mention);
            $parts = preg_split('/\s+/', $mention, 2);

            $query = User::where('name', 'LIKE', $parts[0] . '%');
            if (isset($parts[1])) {
                $query->where('last_name', 'LIKE', $parts[1] . '%');
            }

            $users = $query->where('id', '!=', $currentUserId)->get();

            foreach ($users as $user) {
                if (in_array($user->id, $notified)) {
                    continue;
                }

                Notification::create([
                    'user_id' => $user->id,
                    'from_user_id' => $currentUserId,
                    'type' => 'mention',
                    'title' => $currentUser->full_name . ' te menciono en ' . $client->name,
                    'body' => $interaction->description,
                    'data' => [
                        'client_id' => $client->id,
                        'interaction_id' => $interaction->id,
                        'url' => route('clients.show', $client),
                    ],
                ]);

                $notified[] = $user->id;
            }
        }
    }

    public function createPortalAccount(Request $request, Client $client)
    {
        if ($client->user_id) {
            return back()->with('error', 'Este cliente ya tiene acceso al portal.');
        }

        if (!$client->email) {
            return back()->with('error', 'El cliente necesita un email para crear acceso al portal.');
        }

        $service = app(ClientPortalService::class);
        $result = $service->createPortalAccount($client);

        // Link de activación — el cliente define su propia contraseña, nunca
        // se manda en claro (auditoria 2026-07-06).
        try {
            $service->sendWelcomeInvitation($result['user']);
        } catch (\Exception $e) {
            // Don't fail the account creation if email fails
            \Illuminate\Support\Facades\Log::warning('Portal welcome email failed: ' . $e->getMessage());
        }

        $message = 'Acceso al portal creado y correo de bienvenida enviado.';

        return back()->with('success', $message);
    }

    public function togglePortalAccess(Client $client)
    {
        if (!$client->user_id) {
            return back()->with('error', 'Este cliente no tiene cuenta de portal.');
        }

        $user = \App\Models\User::find($client->user_id);
        if (!$user) {
            return back()->with('error', 'Usuario de portal no encontrado.');
        }

        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'activado' : 'desactivado';
        return back()->with('success', "Acceso al portal {$status}.");
    }

    public function deletePortalAccess(Client $client)
    {
        if (!$client->user_id) {
            return back()->with('error', 'Este cliente no tiene cuenta de portal.');
        }

        $user = \App\Models\User::find($client->user_id);
        $client->update(['user_id' => null]);

        if ($user && $user->role === 'client') {
            $user->delete();
        }

        return back()->with('success', 'Acceso al portal eliminado.');
    }

    public function resetPortalPassword(Request $request, Client $client)
    {
        if (!$client->user_id) {
            return back()->with('error', 'Este cliente no tiene cuenta de portal.');
        }

        $request->validate(['new_password' => 'required|min:6']);

        $user = \App\Models\User::find($client->user_id);
        if (!$user) {
            return back()->with('error', 'Usuario de portal no encontrado.');
        }

        $user->update(['password' => $request->input('new_password')]);

        return back()->with('success', 'Contrasena del portal actualizada.');
    }

    public function search(Request $request)
    {
        $q = $request->input('q', '');
        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $clients = Client::where('name', 'like', "%{$q}%")
            ->orWhere('email', 'like', "%{$q}%")
            ->orWhere('phone', 'like', "%{$q}%")
            ->select('id', 'name', 'email', 'phone')
            ->limit(10)
            ->orderBy('name')
            ->get();

        return response()->json($clients);
    }
}
