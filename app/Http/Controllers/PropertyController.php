<?php

namespace App\Http\Controllers;

use App\Actions\Property\FetchStreetViewPhotoAction;
use App\Models\Broker;
use App\Models\Client;
use App\Models\ClientEmail;
use App\Models\MarketColonia;
use App\Models\Property;
use App\Services\EasyBrokerService;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    public function index(Request $request)
    {
        $query = Property::with('photos', 'owner');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('colony', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        if ($type = $request->input('property_type')) {
            $query->where('property_type', $type);
        }
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        if ($operation = $request->input('operation_type')) {
            $query->where('operation_type', $operation);
        }
        if ($request->filled('price_min')) {
            $query->where('price', '>=', $request->input('price_min'));
        }
        if ($request->filled('price_max')) {
            $query->where('price', '<=', $request->input('price_max'));
        }
        if ($brokerId = $request->input('broker_id')) {
            $query->where('broker_id', $brokerId);
        }

        $properties = $query->latest()->paginate(18)->withQueryString();
        $brokers = Broker::where('status', 'active')->orderBy('name')->get();

        $stats = [
            'total'     => Property::count(),
            'captacion' => Property::where('status', 'captacion')->count(),
            'available' => Property::where('status', 'available')->count(),
            'reserved'  => Property::where('status', 'reserved')->count(),
            'sold'      => Property::where('status', 'sold')->count(),
            'rented'    => Property::where('status', 'rented')->count(),
        ];

        if ($request->ajax()) {
            return view('properties._grid', compact('properties'))->render();
        }

        return view('properties.index', compact('properties', 'brokers', 'stats'));
    }

    public function create()
    {
        $brokers = Broker::where('status', 'active')->orderBy('name')->get();
        $clients = Client::orderBy('name')->select('id', 'name', 'email')->get();
        $colonias = MarketColonia::with('zone')->published()->orderBy('name')->get()->groupBy('zone.name');
        return view('properties.create', compact('brokers', 'clients', 'colonias'));
    }

    public function show(Property $property)
    {
        $property->load('photos', 'broker', 'owner');

        // Deals (pipeline) with client and broker
        $deals = $property->deals()->with('client', 'broker')->latest()->get();

        // Operations linked to this property
        $operations = $property->operations()->with('client', 'secondaryClient', 'user')->latest()->get();

        // Interactions related to this property
        $interactions = $property->interactions()->with('client', 'user')->latest()->limit(20)->get();

        // Emails that included this property
        $emails = ClientEmail::whereJsonContains('property_ids', $property->id)
            ->with('client', 'user')
            ->latest()
            ->get();

        // Unique interested clients from deals + operations + interactions
        $interestedClients = collect()
            ->merge($deals->pluck('client'))
            ->merge($operations->pluck('client'))
            ->merge($interactions->pluck('client'))
            ->merge($emails->pluck('client'))
            ->filter()
            ->unique('id')
            ->values();

        // Opiniones de valor vinculadas a esta propiedad
        $valuations = $property->valuations()->with('creator', 'colonia')->latest()->get();

        $clients = Client::orderBy('name')->select('id', 'name', 'email')->get();
        $users = \App\Models\User::where('is_active', true)->orderBy('name')->select('id', 'name', 'email', 'phone')->get();

        return view('properties.show', compact(
            'property', 'deals', 'operations', 'interactions', 'emails', 'interestedClients', 'valuations', 'clients', 'users'
        ));
    }

    public function store(Request $request)
    {
        $request->merge([
            'client_id' => $request->input('client_id') ?: null,
            'broker_id' => $request->input('broker_id') ?: null,
        ]);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'colony' => 'nullable|string|max:100',
            'zipcode' => 'nullable|string|max:10',
            'bedrooms' => 'nullable|integer|min:0',
            'bathrooms' => 'nullable|integer|min:0',
            'half_bathrooms' => 'nullable|integer|min:0',
            'area' => 'nullable|numeric|min:0',
            'construction_area' => 'nullable|numeric|min:0',
            'lot_area' => 'nullable|numeric|min:0',
            'parking' => 'nullable|integer|min:0',
            'floors' => 'nullable|integer|min:0',
            'year_built' => 'nullable|integer|min:1900|max:' . date('Y'),
            'maintenance_fee' => 'nullable|numeric|min:0',
            'furnished' => 'nullable|string|in:sin_amueblar,semi_amueblado,amueblado',
            'amenities' => 'nullable|array',
            'amenities.*' => 'string',
            'status' => 'nullable|in:captacion,available,reserved,sold,rented',
            'property_type' => 'nullable|string|in:House,Apartment,Land,Office,Commercial,Warehouse,Building',
            'operation_type' => 'nullable|string|in:sale,rental,temporary_rental',
            'currency' => 'nullable|string|in:MXN,USD',
            'description' => 'nullable|string',
            'broker_id' => 'nullable|exists:brokers,id',
            'client_id' => 'nullable|exists:clients,id',
            'market_colonia_id' => 'nullable|exists:market_colonias,id',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'photos' => 'nullable|array|max:20',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'youtube_url' => 'nullable|url|max:500',
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('properties', 'public');
        }

        $validated['status'] = $validated['status'] ?? 'captacion';

        unset($validated['photos']);
        $property = Property::create($validated);

        // Handle multiple photos
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $i => $file) {
                $path = $file->store('properties/photos', 'public');
                $property->photos()->create([
                    'path' => $path,
                    'is_primary' => $i === 0,
                    'sort_order' => $i + 1,
                ]);
                if ($i === 0) {
                    $property->update(['photo' => $path]);
                }
            }
        }

        return redirect()->route('properties.edit', $property)->with('success', 'Propiedad creada exitosamente.');
    }

    public function edit(Property $property)
    {
        $brokers = Broker::where('status', 'active')->orderBy('name')->get();
        $clients = Client::orderBy('name')->select('id', 'name', 'email')->get();
        $colonias = MarketColonia::with('zone')->published()->orderBy('name')->get()->groupBy('zone.name');
        $property->load('photos', 'owner');
        return view('properties.edit', compact('property', 'brokers', 'clients', 'colonias'));
    }

    public function update(Request $request, Property $property)
    {
        $request->merge([
            'client_id' => $request->input('client_id') ?: null,
            'broker_id' => $request->input('broker_id') ?: null,
        ]);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'colony' => 'nullable|string|max:100',
            'zipcode' => 'nullable|string|max:10',
            'bedrooms' => 'nullable|integer|min:0',
            'bathrooms' => 'nullable|integer|min:0',
            'half_bathrooms' => 'nullable|integer|min:0',
            'area' => 'nullable|numeric|min:0',
            'construction_area' => 'nullable|numeric|min:0',
            'lot_area' => 'nullable|numeric|min:0',
            'parking' => 'nullable|integer|min:0',
            'floors' => 'nullable|integer|min:0',
            'year_built' => 'nullable|integer|min:1900|max:' . date('Y'),
            'maintenance_fee' => 'nullable|numeric|min:0',
            'furnished' => 'nullable|string|in:sin_amueblar,semi_amueblado,amueblado',
            'amenities' => 'nullable|array',
            'amenities.*' => 'string',
            'status' => 'nullable|in:captacion,available,reserved,sold,rented',
            'property_type' => 'nullable|string|in:House,Apartment,Land,Office,Commercial,Warehouse,Building',
            'operation_type' => 'nullable|string|in:sale,rental,temporary_rental',
            'currency' => 'nullable|string|in:MXN,USD',
            'description' => 'nullable|string',
            'broker_id' => 'nullable|exists:brokers,id',
            'client_id' => 'nullable|exists:clients,id',
            'market_colonia_id' => 'nullable|exists:market_colonias,id',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'youtube_url' => 'nullable|url|max:500',
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('properties', 'public');
        }

        // Ensure nullable FK fields are explicitly set (even as null) so they get saved
        if (!array_key_exists('client_id', $validated)) {
            $validated['client_id'] = null;
        }
        if (!array_key_exists('broker_id', $validated)) {
            $validated['broker_id'] = null;
        }

        $property->update($validated);

        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('properties.edit', $property)->with('success', 'Propiedad actualizada exitosamente');
    }

    public function destroy(Property $property)
    {
        $property->delete();
        return redirect()->route('properties.index')->with('success', 'Propiedad eliminada exitosamente');
    }

    public function fetchStreetView(Property $property, FetchStreetViewPhotoAction $action)
    {
        $property->load('photos');
        $saved = $action->execute($property, forceReplace: false);

        if (request()->expectsJson()) {
            return response()->json([
                'success' => $saved,
                'message' => $saved ? 'Foto de fachada generada.' : 'No se encontró imagery de Street View para esta dirección.',
                'photo_url' => $saved ? $property->photos()->latest()->first()?->path : null,
            ]);
        }

        return back()->with(
            $saved ? 'success' : 'warning',
            $saved ? 'Foto de fachada generada desde Street View.' : 'No hay imágenes de Street View para esta dirección. Verifica que la dirección sea correcta.'
        );
    }

    public function publishToEasyBroker(Property $property, EasyBrokerService $ebService)
    {
        $result = $ebService->publish($property);

        if (request()->expectsJson()) {
            return response()->json([
                'success'    => $result['success'],
                'message'    => $result['message'],
                'eb_status'  => $property->fresh()->easybroker_status,
                'public_url' => $property->fresh()->easybroker_public_url,
            ]);
        }

        return back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function unpublishFromEasyBroker(Property $property, EasyBrokerService $ebService)
    {
        $result = $ebService->unpublish($property);

        if (request()->expectsJson()) {
            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
            ]);
        }

        return back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    public function toggleFeatured(Property $property)
    {
        $property->update(['is_featured' => ! $property->is_featured]);

        $msg = $property->is_featured
            ? "✦ \"{$property->title}\" ahora es Destacada y aparece en el home."
            : "\"{$property->title}\" ya no es Destacada.";

        return back()->with('success', $msg);
    }

    public function scheduleVisit(Request $request, Property $property)
    {
        $validated = $request->validate([
            'client_id'               => 'required|exists:clients,id',
            'description'             => 'nullable|string|max:1000',
            'scheduled_at_date'       => 'required|date',
            'scheduled_at_time'       => 'required|date_format:H:i',
            'duracion'                => 'nullable|integer|in:30,60,90,120',
            'asesor_id'               => 'nullable|exists:users,id',
            'send_confirmation_email' => 'nullable|boolean',
        ]);

        $client = Client::findOrFail($validated['client_id']);
        $scheduled = \Carbon\Carbon::parse($validated['scheduled_at_date'] . ' ' . $validated['scheduled_at_time']);

        // Resolve asesor user data
        $asesorUser   = !empty($validated['asesor_id']) ? \App\Models\User::find($validated['asesor_id']) : null;
        $asesorNombre = $asesorUser?->name ?? auth()->user()->name ?? 'Tu asesor';
        $asesorEmail  = $asesorUser?->email ?? '';
        $asesorPhone  = $asesorUser?->phone ?? '';

        $interaction = \App\Models\Interaction::create([
            'client_id'               => $client->id,
            'property_id'             => $property->id,
            'user_id'                 => \Illuminate\Support\Facades\Auth::id(),
            'type'                    => 'visit',
            'description'             => $validated['description'] ?? "Visita agendada para {$property->address}",
            'scheduled_at'            => $scheduled,
            'visit_token'             => \Illuminate\Support\Str::uuid()->toString(),
            'send_confirmation_email' => $request->boolean('send_confirmation_email', true),
        ]);

        // Lead scoring
        app(\App\Services\LeadScoringService::class)->processEvent($client->id, 'visit_scheduled', ['source' => 'interaction']);

        // Passive scoring for property owner
        if ($property->owner && $property->owner->id !== $client->id) {
            app(\App\Services\LeadScoringService::class)->processEvent(
                $property->owner->id,
                'message_sent',
                ['source' => 'property_visit_scheduled', 'property_id' => $property->id]
            );
        }

        // Send confirmation email
        if ($interaction->send_confirmation_email && $client->email) {
            try {
                $duracion = (string) ($validated['duracion'] ?? '30');
                $addressParts = array_filter([$property->address ?? '', $property->colony ?? '', $property->city ?? 'CDMX']);
                $address = urlencode(implode(', ', $addressParts));
                $mapsUrl = $address ? "https://www.google.com/maps/search/?api=1&query={$address}" : '';

                \Illuminate\Support\Facades\Mail::to($client->email)->send(
                    new \App\Mail\V4\Mailables\CitaMail(
                        new \App\Mail\V4\Data\CitaData(
                            email: $client->email,
                            nombre: $client->name,
                            dia_semana: $scheduled->locale('es')->dayName,
                            dia: (string) $scheduled->day,
                            mes: $scheduled->locale('es')->monthName,
                            anio: (string) $scheduled->year,
                            hora: $scheduled->format('g:i A'),
                            duracion: $duracion,
                            direccion: $property->address ?? 'A coordinar',
                            colonia: $property->colony ?? '',
                            asesor: $asesorNombre,
                            visit_token: $interaction->visit_token,
                            maps_url: $mapsUrl,
                            asesor_email: $asesorEmail,
                            asesor_phone: $asesorPhone,
                        )
                    )
                );
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('Visit confirmation email failed: ' . $e->getMessage());
            }
        }

        return redirect()->route('properties.show', $property)->with('success', "Visita agendada para {$client->name} el {$scheduled->format('d/m/Y')} a las {$scheduled->format('H:i')}.");
    }
}
