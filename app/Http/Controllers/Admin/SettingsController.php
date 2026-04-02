<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = SiteSetting::first();
        return view('admin.settings', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'site_name' => 'required|string|max:255',
            'site_tagline' => 'nullable|string|max:255',
            'primary_color' => 'required|string',
            'secondary_color' => 'required|string',
            'home_welcome_text' => 'nullable|string',
            'logo_type' => 'nullable|in:text,image',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ]);

        $settings = SiteSetting::first();

        // Handle logo upload
        if ($request->hasFile('logo')) {
            if ($settings && $settings->logo_path) {
                Storage::disk('public')->delete($settings->logo_path);
            }
            $validated['logo_path'] = $request->file('logo')->store('logos', 'public');
            $validated['logo_type'] = 'image';
        }

        // If switching to text, keep logo_path but set type to text
        if ($request->input('logo_type') === 'text') {
            $validated['logo_type'] = 'text';
        }

        unset($validated['logo']);

        if ($settings) {
            $settings->update($validated);
        } else {
            SiteSetting::create($validated);
        }

        cache()->forget('site_settings');

        return back()->with('success', 'Configuracion actualizada correctamente');
    }
}
