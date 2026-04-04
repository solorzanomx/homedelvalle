<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;

class ServiciosPageController extends Controller
{
    public function index()
    {
        $settings = SiteSetting::first();

        return view('admin.servicios-page', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'heading' => 'nullable|string|max:255',
            'subheading' => 'nullable|string|max:500',
            'services' => 'nullable|array|max:6',
            'services.*.title' => 'nullable|string|max:255',
            'services.*.slug' => 'nullable|string|max:100',
            'services.*.description' => 'nullable|string|max:1000',
            'services.*.icon' => 'nullable|string|max:50',
            'services.*.features' => 'nullable|array|max:5',
            'services.*.features.*' => 'nullable|string|max:200',
            'services.*.cta_text' => 'nullable|string|max:100',
            'services.*.cta_url' => 'nullable|string|max:255',
            'cta_heading' => 'nullable|string|max:255',
            'cta_subheading' => 'nullable|string|max:500',
        ]);

        $services = collect($validated['services'] ?? [])
            ->filter(fn($s) => !empty($s['title']))
            ->map(function ($s) {
                $s['features'] = array_values(array_filter($s['features'] ?? []));
                return $s;
            })
            ->values()
            ->toArray();

        $content = [
            'heading' => $validated['heading'] ?? null,
            'subheading' => $validated['subheading'] ?? null,
            'services' => $services,
            'cta_heading' => $validated['cta_heading'] ?? null,
            'cta_subheading' => $validated['cta_subheading'] ?? null,
        ];

        $settings = SiteSetting::first();
        if ($settings) {
            $settings->update(['servicios_content' => $content]);
        } else {
            SiteSetting::create(['servicios_content' => $content]);
        }

        cache()->forget('site_settings');

        return back()->with('success', 'Pagina de Servicios actualizada correctamente.');
    }
}
