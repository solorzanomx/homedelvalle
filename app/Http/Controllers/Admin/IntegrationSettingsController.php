<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class IntegrationSettingsController extends Controller
{
    public function index()
    {
        $settings = SiteSetting::first();
        return view('admin.integrations.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'gtm_id' => 'nullable|string|max:50',
            'google_analytics_id' => 'nullable|string|max:50',
            'facebook_pixel_id' => 'nullable|string|max:50',
            'custom_head_scripts' => 'nullable|string|max:10000',
            'custom_body_scripts' => 'nullable|string|max:10000',
        ]);

        $validated['gtm_enabled'] = $request->boolean('gtm_enabled');
        $validated['ga_enabled'] = $request->boolean('ga_enabled');
        $validated['fb_pixel_enabled'] = $request->boolean('fb_pixel_enabled');
        $validated['webhook_enabled'] = $request->boolean('webhook_enabled');

        $settings = SiteSetting::first();

        if ($settings) {
            $settings->update($validated);
        } else {
            SiteSetting::create($validated);
        }

        cache()->forget('site_settings');

        return back()->with('success', 'Integraciones actualizadas correctamente.');
    }

    public function regenerateWebhookKey()
    {
        $settings = SiteSetting::first();

        if (!$settings) {
            $settings = SiteSetting::create([]);
        }

        $key = 'whk_' . bin2hex(random_bytes(32));
        $settings->update([
            'webhook_api_key' => $key,
            'webhook_enabled' => true,
        ]);

        cache()->forget('site_settings');

        if (request()->expectsJson()) {
            return response()->json(['key' => $key, 'message' => 'API Key regenerada exitosamente.']);
        }

        return back()->with('success', 'API Key regenerada exitosamente. Actualiza la clave en n8n.');
    }
}
