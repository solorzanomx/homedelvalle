<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EasyBrokerSetting;
use App\Services\EasyBrokerService;
use Illuminate\Http\Request;

class EasyBrokerSettingsController extends Controller
{
    public function index()
    {
        $ebSettings = EasyBrokerSetting::first();

        // If encrypted api_key can't be decrypted (APP_KEY changed), clear it
        if ($ebSettings) {
            try {
                $ebSettings->api_key;
            } catch (\Illuminate\Contracts\Encryption\DecryptException|\Illuminate\Contracts\Encryption\DecryptionException|\RuntimeException $e) {
                $ebSettings->update(['api_key' => null]);
                $ebSettings->refresh();
                session()->flash('warning', 'La API Key de EasyBroker se invalido por un cambio de clave del sistema. Por favor ingresala de nuevo.');
            }
        }

        return view('admin.easybroker.settings', compact('ebSettings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'api_key'                  => 'nullable|string|max:500',
            'base_url'                 => 'required|url|max:255',
            'auto_publish'             => 'boolean',
            'default_property_type'    => 'required|string|in:House,Apartment,Land,Office,Commercial,Warehouse,Building',
            'default_operation_type'   => 'required|string|in:sale,rental,temporary_rental',
            'default_currency'         => 'required|string|in:MXN,USD',
            'default_city_id'          => 'nullable|string|max:100',
            'default_admin_division_id'=> 'nullable|string|max:100',
            'default_latitude'         => 'nullable|numeric|between:-90,90',
            'default_longitude'        => 'nullable|numeric|between:-180,180',
        ]);

        $validated['auto_publish'] = $request->boolean('auto_publish');

        $settings = EasyBrokerSetting::first();

        if ($settings) {
            if (empty($validated['api_key'])) {
                unset($validated['api_key']);
            }
            $settings->update($validated);
        } else {
            EasyBrokerSetting::create($validated);
        }

        return back()->with('success', 'Configuracion de EasyBroker actualizada.');
    }

    public function test(EasyBrokerService $ebService)
    {
        $result = $ebService->testConnection();

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }

    public function searchLocations(Request $request, EasyBrokerService $ebService)
    {
        $q = $request->input('q', '');
        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $result = $ebService->searchLocations($q);
        return response()->json($result['data'] ?? []);
    }
}
