<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Http\Request;

class NosotrosPageController extends Controller
{
    public function index()
    {
        $settings = SiteSetting::first();
        $users = User::where('is_active', true)
            ->whereNotIn('role', ['client'])
            ->orderByDesc('show_on_website')
            ->orderBy('website_order')
            ->orderBy('name')
            ->get();

        return view('admin.nosotros-page', compact('settings', 'users'));
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

    public function toggleTeamMember(Request $request, User $user)
    {
        $user->update([
            'show_on_website' => !$user->show_on_website,
        ]);

        $status = $user->show_on_website ? 'visible' : 'oculto';

        return back()->with('success', "{$user->full_name} ahora esta {$status} en el sitio web.");
    }

    public function updateTeamOrder(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'order' => 'required|integer|min:0|max:99',
        ]);

        User::where('id', $request->user_id)->update(['website_order' => $request->order]);

        return response()->json(['success' => true]);
    }
}
