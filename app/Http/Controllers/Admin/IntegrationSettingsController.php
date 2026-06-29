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
            'gtm_id'                => 'nullable|string|max:50',
            'google_analytics_id'   => 'nullable|string|max:50',
            'facebook_pixel_id'     => 'nullable|string|max:50',
            'custom_head_scripts'   => 'nullable|string|max:10000',
            'custom_body_scripts'   => 'nullable|string|max:10000',
            'fb_app_id'             => 'nullable|string|max:100',
            'fb_app_secret'         => 'nullable|string|max:100',
            'fb_page_id'            => 'nullable|string|max:100',
            'fb_page_access_token'  => 'nullable|string|max:5000',
        ]);

        $validated['gtm_enabled']      = $request->boolean('gtm_enabled');
        $validated['ga_enabled']       = $request->boolean('ga_enabled');
        $validated['fb_pixel_enabled'] = $request->boolean('fb_pixel_enabled');
        $validated['webhook_enabled']  = $request->boolean('webhook_enabled');
        $validated['fb_api_enabled']   = $request->boolean('fb_api_enabled');

        $settings = SiteSetting::first();

        if ($settings) {
            $settings->update($validated);
        } else {
            SiteSetting::create($validated);
        }

        cache()->forget('site_settings');

        return back()->with('success', 'Integraciones actualizadas correctamente.');
    }

    public function testFacebookConnection(Request $request)
    {
        $token  = $request->input('token') ?? SiteSetting::first()?->fb_page_access_token;
        $pageId = $request->input('page_id') ?? SiteSetting::first()?->fb_page_id;

        if (! $token) {
            return response()->json(['success' => false, 'error' => 'No hay Access Token configurado.']);
        }

        try {
            $res = \Illuminate\Support\Facades\Http::timeout(10)
                ->get("https://graph.facebook.com/v21.0/{$pageId}", [
                    'fields'       => 'id,name,fan_count',
                    'access_token' => $token,
                ]);

            if (! $res->successful()) {
                $msg = $res->json('error.message') ?? 'Token inválido';
                return response()->json(['success' => false, 'error' => $msg]);
            }

            $page = $res->json();
            return response()->json([
                'success' => true,
                'name'    => $page['name'] ?? 'Página desconocida',
                'id'      => $page['id'] ?? $pageId,
                'fans'    => $page['fan_count'] ?? null,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
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
