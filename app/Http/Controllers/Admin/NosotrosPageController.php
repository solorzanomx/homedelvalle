<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;

class NosotrosPageController extends Controller
{
    public function index()
    {
        $settings = SiteSetting::first();

        return view('admin.nosotros-page', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'about_text' => 'nullable|string|max:3000',
            'mission' => 'nullable|string|max:1000',
            'vision' => 'nullable|string|max:1000',
            'story_heading' => 'nullable|string|max:255',
            'philosophy_heading' => 'nullable|string|max:255',
            'philosophy_text' => 'nullable|string|max:2000',
            'values' => 'nullable|array|max:6',
            'values.*.title' => 'nullable|string|max:100',
            'values.*.description' => 'nullable|string|max:300',
            'stats' => 'nullable|array|max:6',
            'stats.*.value' => 'nullable|string|max:20',
            'stats.*.label' => 'nullable|string|max:100',
            'team_heading' => 'nullable|string|max:255',
            'team_subheading' => 'nullable|string|max:500',
        ]);

        $settings = SiteSetting::first();

        $content = [
            'mission' => $validated['mission'] ?? null,
            'vision' => $validated['vision'] ?? null,
            'story_heading' => $validated['story_heading'] ?? null,
            'philosophy_heading' => $validated['philosophy_heading'] ?? null,
            'philosophy_text' => $validated['philosophy_text'] ?? null,
            'values' => array_values(array_filter($validated['values'] ?? [], fn($v) => !empty($v['title']))),
            'stats' => array_values(array_filter($validated['stats'] ?? [], fn($s) => !empty($s['value']))),
            'team_heading' => $validated['team_heading'] ?? null,
            'team_subheading' => $validated['team_subheading'] ?? null,
        ];

        $data = [
            'nosotros_content' => $content,
            'about_text' => $validated['about_text'] ?? null,
        ];

        if ($settings) {
            $settings->update($data);
        } else {
            SiteSetting::create($data);
        }

        cache()->forget('site_settings');

        return back()->with('success', 'Pagina Nosotros actualizada correctamente.');
    }
}
