<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;

class VenderPageController extends Controller
{
    public function index()
    {
        $settings = SiteSetting::first();

        return view('admin.vender-page', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'badge' => 'nullable|string|max:255',
            'heading' => 'nullable|string|max:255',
            'subheading' => 'nullable|string|max:500',
            'benefits' => 'nullable|array|max:6',
            'benefits.*.icon' => 'nullable|string|max:50',
            'benefits.*.title' => 'nullable|string|max:100',
            'benefits.*.desc' => 'nullable|string|max:300',
            'metrics' => 'nullable|array|max:4',
            'metrics.*.value' => 'nullable|string|max:20',
            'metrics.*.label' => 'nullable|string|max:100',
            'process_steps' => 'nullable|array|max:5',
            'process_steps.*.num' => 'nullable|string|max:5',
            'process_steps.*.title' => 'nullable|string|max:100',
            'process_steps.*.desc' => 'nullable|string|max:300',
            'faqs' => 'nullable|array|max:8',
            'faqs.*.q' => 'nullable|string|max:255',
            'faqs.*.a' => 'nullable|string|max:1000',
            'cta_heading' => 'nullable|string|max:255',
            'cta_subheading' => 'nullable|string|max:500',
            'wa_message' => 'nullable|string|max:255',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
        ]);

        $content = [
            'badge' => $validated['badge'] ?? null,
            'heading' => $validated['heading'] ?? null,
            'subheading' => $validated['subheading'] ?? null,
            'benefits' => array_values(array_filter($validated['benefits'] ?? [], fn($b) => !empty($b['title']))),
            'metrics' => array_values(array_filter($validated['metrics'] ?? [], fn($m) => !empty($m['value']))),
            'process_steps' => array_values(array_filter($validated['process_steps'] ?? [], fn($s) => !empty($s['title']))),
            'faqs' => array_values(array_filter($validated['faqs'] ?? [], fn($f) => !empty($f['q']))),
            'cta_heading' => $validated['cta_heading'] ?? null,
            'cta_subheading' => $validated['cta_subheading'] ?? null,
            'wa_message' => $validated['wa_message'] ?? null,
            'meta_title' => $validated['meta_title'] ?? null,
            'meta_description' => $validated['meta_description'] ?? null,
        ];

        $settings = SiteSetting::first();
        if ($settings) {
            $settings->update(['vender_content' => $content]);
        } else {
            SiteSetting::create(['vender_content' => $content]);
        }

        cache()->forget('site_settings');

        return back()->with('success', 'Pagina Vender actualizada correctamente.');
    }
}
