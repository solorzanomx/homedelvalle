<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;

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

        $settings = SiteSetting::first();

        if ($settings) {
            $settings->update($validated);
        } else {
            SiteSetting::create($validated);
        }

        cache()->forget('site_settings');

        return back()->with('success', 'Integraciones actualizadas correctamente.');
    }
}
