<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;

class FooterController extends Controller
{
    public function index()
    {
        $settings = SiteSetting::first();

        return view('admin.footer', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'footer_about' => 'nullable|string|max:500',
            'footer_bottom_text' => 'nullable|string|max:255',
            'footer_links' => 'nullable|array|max:6',
            'footer_links.*.label' => 'nullable|string|max:100',
            'footer_links.*.url' => 'nullable|string|max:255',
        ]);

        $settings = SiteSetting::first();

        $footerLinks = collect($validated['footer_links'] ?? [])->filter(fn($l) => !empty($l['label']))->values()->toArray();
        unset($validated['footer_links']);
        $validated['footer_bottom_links'] = $footerLinks;

        if ($settings) {
            $settings->update($validated);
        } else {
            SiteSetting::create($validated);
        }

        cache()->forget('site_settings');

        return back()->with('success', 'Footer actualizado correctamente.');
    }
}
