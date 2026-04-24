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
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:clients',
            'phone' => 'nullable|string|max:20',
            'whatsapp' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'curp' => 'nullable|string|max:18',
            'rfc' => 'nullable|string|max:13',
            'interest_types' => 'nullable|array',
            'interest_types.*' => 'in:compra,venta,renta_propietario,renta_inquilino',
            'lead_temperature' => 'nullable|in:frio,tibio,caliente',
            'priority' => 'nullable|in:alta,media,baja',
            'initial_notes' => 'nullable|string|max:2000',
            'budget_min' => 'nullable|numeric',
            'budget_max' => 'nullable|numeric',
            'property_type' => 'nullable|string',
            'broker_id' => 'nullable|exists:brokers,id',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'marketing_channel_id' => 'nullable|exists:marketing_channels,id',
            'marketing_campaign_id' => 'nullable|exists:marketing_campaigns,id',
            'acquisition_cost' => 'nullable|numeric|min:0',
        ]);

        $validated['assigned_user_id'] = Auth::id();

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
        $interactions = $client->interactions()->with('user')->latest()->get();

        // Properties where this client is the owner
        $ownedProperties = $client->ownedProperties()->with('broker')->get();

        // Properties linked via deals (interested)
        $dealProperties = Property::whereHas('deals', fn($q) => $q->where('client_id', $client->id))
            ->with('broker')
            ->get();

        // Properties sent via email
        $emailPropertyIds = $emails->pluck('property_ids')->filter()->flatten()->unique()->values();
        $emailProperties = $emailPropertyIds->isNotEmpty()
            ? Property::whereIn('id', $emailPropertyIds)->get()
            : collect();
        $interactions = $client->interactions()->with('user')->latest()->get();

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
            'note' => ['dot' => 'note', 'color' => '#8b5cf6', 'label' => 'Nota'],
            'call' => ['dot' => 'call', 'color' => '#10b981', 'label' => 'Llamada'],
            'visit' => ['dot' => 'visit', 'color' => '#f59e0b', 'label' => 'Visita'],
            'meeting' => ['dot' => 'meeting', 'color' => '#ef4444', 'label' => 'Reunion'],
            'whatsapp' => ['dot' => 'whatsapp', 'color' => '#25d366', 'label' => 'WhatsApp'],
            'email' => ['dot' => 'email', 'color' => '#3b82f6', 'label' => 'Correo'],
        ];

        foreach ($interactions as $interaction) {
            $config = $typeConfig[$interaction->type] ?? ['dot' => 'system', 'color' => '#94a3b8', 'label' => ucfirst($interaction->type)];

            $timeline->push([
                'date' => $interaction->created_at,
                'dot' => $config['dot'],
                'color' => $config['color'],
                'type_label' => $config['label'],
                'body' => MentionHelper::render($interaction->description),
                'meta' => 'Por ' . e($interaction->user->name ?? ''),
            ]);
        }

        $timeline = $timeline->sortByDesc('date')->values();

        $confidencialidadRequest = \App\Models\GoogleSignatureRequest::where('contacto_id', $client->id)
            ->where('tipo', 'confidencialidad')
            ->latest()
            ->first();

        return view('clients.show', compact('client', 'emails', 'interactions', 'emailsSent', 'emailsOpened', 'timeline', 'ownedProperties', 'dealProperties', 'emailProperties', 'confidencialidadRequest'));
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
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:clients,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'whatsapp' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'curp' => 'nullable|string|max:18',
            'rfc' => 'nullable|string|max:13',
            'interest_types' => 'nullable|array',
            'interest_types.*' => 'in:compra,venta,renta_propietario,renta_inquilino',
            'lead_temperature' => 'nullable|in:frio,tibio,caliente',
            'priority' => 'nullable|in:alta,media,baja',
            'initial_notes' => 'nullable|string|max:2000',
            'budget_min' => 'nullable|numeric',
            'budget_max' => 'nullable|numeric',
            'property_type' => 'nullable|string',
            'broker_id' => 'nullable|exists:brokers,id',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'marketing_channel_id' => 'nullable|exists:marketing_channels,id',
            'marketing_campaign_id' => 'nullable|exists:marketing_campaigns,id',
            'acquisition_cost' => 'nullable|numeric|min:0',
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('clients', 'public');
        }

        // Ensure interest_types is cleared when no checkboxes are selected
        if (!$request->has('interest_types')) {
            $validated['interest_types'] = [];
        }

        $client->update($validated);
        return redirect()->route('clients.edit', $client)->with('success', 'Cliente actualizado exitosamente');
    }

    public function destroy(string $id)
    {
        $client = Client::findOrFail($id);
        $this->authorize('delete', $client);
        $client->delete();
        return redirect()->route('clients.index')->with('success', 'Cliente eliminado exitosamente');
    }

    /**
     * Store a quick interaction/note for a client.
     */
    public function storeInteraction(Request $request, Client $client)
    {
        $validated = $request->validate([
            'type' => 'required|in:note,call,visit,meeting,whatsapp',
            'description' => 'required|string|max:1000',
        ]);

        $interaction = Interaction::create([
            'client_id' => $client->id,
            'user_id' => Auth::id(),
            'type' => $validated['type'],
            'description' => $validated['description'],
            'completed_at' => now(),
        ]);

        // Score the interaction
        $eventMap = ['call' => 'call_completed', 'visit' => 'visit_completed', 'meeting' => 'visit_completed', 'whatsapp' => 'message_sent'];
        $scoringEvent = $eventMap[$validated['type']] ?? null;
        if ($scoringEvent) {
            app(\App\Services\LeadScoringService::class)->processEvent($client->id, $scoringEvent, ['source' => 'interaction']);
        }

        // Parse @mentions and create notifications
        $this->processMentions($validated['description'], $interaction, $client);

        return redirect()->route('clients.show', $client)->with('success', 'Nota agregada.');
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

        $password = $request->input('password');

        $service = new ClientPortalService();
        $result = $service->createPortalAccount($client, $password);

        // Send welcome email
        try {
            $emailService = app(\App\Services\EmailService::class);
            $emailService->sendPortalWelcome($client->name, $client->email, $result['password']);
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
