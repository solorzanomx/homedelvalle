<?php

namespace App\Http\Controllers;

use App\Models\Broker;
use App\Models\Client;
use App\Models\ClientEmail;
use App\Models\Property;
use App\Services\EasyBrokerService;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    public function index(Request $request)
    {
        $query = Property::with('photos');

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

        $properties = $query->latest()->paginate(12)->withQueryString();
        $brokers = Broker::where('status', 'active')->orderBy('name')->get();
        return view('properties.index', compact('properties', 'brokers'));
    }

    public function create()
    {
        $brokers = Broker::where('status', 'active')->orderBy('name')->get();
        $clients = Client::orderBy('name')->select('id', 'name', 'email')->get();
        return view('properties.create', compact('brokers', 'clients'));
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

        return view('properties.show', compact(
            'property', 'deals', 'operations', 'interactions', 'emails', 'interestedClients'
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
            'status' => 'nullable|in:available,sold,rented',
            'property_type' => 'nullable|string|in:House,Apartment,Land,Office,Commercial,Warehouse,Building',
            'operation_type' => 'nullable|string|in:sale,rental,temporary_rental',
            'currency' => 'nullable|string|in:MXN,USD',
            'description' => 'nullable|string',
            'broker_id' => 'nullable|exists:brokers,id',
            'client_id' => 'nullable|exists:clients,id',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'photos' => 'nullable|array|max:20',
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'youtube_url' => 'nullable|url|max:500',
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('properties', 'public');
        }

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
        $property->load('photos', 'owner');
        return view('properties.edit', compact('property', 'brokers', 'clients'));
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
            'status' => 'nullable|in:available,sold,rented',
            'property_type' => 'nullable|string|in:House,Apartment,Land,Office,Commercial,Warehouse,Building',
            'operation_type' => 'nullable|string|in:sale,rental,temporary_rental',
            'currency' => 'nullable|string|in:MXN,USD',
            'description' => 'nullable|string',
            'broker_id' => 'nullable|exists:brokers,id',
            'client_id' => 'nullable|exists:clients,id',
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
        return redirect()->route('properties.edit', $property)->with('success', 'Propiedad actualizada exitosamente');
    }

    public function destroy(Property $property)
    {
        $property->delete();
        return redirect()->route('properties.index')->with('success', 'Propiedad eliminada exitosamente');
    }

    public function publishToEasyBroker(Property $property, EasyBrokerService $ebService)
    {
        $result = $ebService->publish($property);

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }

    public function unpublishFromEasyBroker(Property $property, EasyBrokerService $ebService)
    {
        $result = $ebService->unpublish($property);

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }
}
