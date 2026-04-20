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
            'whatsapp_number' => 'nullable|string|max:20',
            'contact_phone' => 'nullable|string|max:30',
            'contact_email' => 'nullable|string|email|max:255',
            'address' => 'nullable|string|max:500',
            'facebook_url' => 'nullable|string|url|max:500',
            'instagram_url' => 'nullable|string|url|max:500',
            'tiktok_url' => 'nullable|string|url|max:500',
            'google_maps_embed' => 'nullable|string|max:2000',
            'logo_type' => 'nullable|in:text,image',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'favicon' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
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

        // Handle favicon upload — crop to circle with transparent background
        if ($request->hasFile('favicon')) {
            if ($settings && $settings->favicon_path) {
                Storage::disk('public')->delete($settings->favicon_path);
            }
            $validated['favicon_path'] = $this->processCircularFavicon($request->file('favicon'));
        }

        unset($validated['logo']);
        unset($validated['favicon']);

        if ($settings) {
            $settings->update($validated);
        } else {
            SiteSetting::create($validated);
        }

        cache()->forget('site_settings');

        return back()->with('success', 'Configuracion actualizada correctamente');
    }

    private function processCircularFavicon($file): string
    {
        $size = 180;
        $src = imagecreatefromstring(file_get_contents($file->getPathname()));
        $w = imagesx($src);
        $h = imagesy($src);

        // Make square by cropping center
        $min = min($w, $h);
        $square = imagecreatetruecolor($size, $size);
        imagealphablending($square, false);
        imagesavealpha($square, true);
        $transparent = imagecolorallocatealpha($square, 0, 0, 0, 127);
        imagefill($square, 0, 0, $transparent);

        // Resize to target size
        imagecopyresampled($square, $src, 0, 0, (int)(($w - $min) / 2), (int)(($h - $min) / 2), $size, $size, $min, $min);
        imagedestroy($src);

        // Apply circular mask
        $circle = imagecreatetruecolor($size, $size);
        imagealphablending($circle, false);
        imagesavealpha($circle, true);
        $transparent = imagecolorallocatealpha($circle, 0, 0, 0, 127);
        imagefill($circle, 0, 0, $transparent);

        $radius = $size / 2;
        for ($x = 0; $x < $size; $x++) {
            for ($y = 0; $y < $size; $y++) {
                $dist = sqrt(pow($x - $radius, 2) + pow($y - $radius, 2));
                if ($dist <= $radius) {
                    $color = imagecolorat($square, $x, $y);
                    imagesetpixel($circle, $x, $y, $color);
                }
            }
        }
        imagedestroy($square);

        $dir = storage_path('app/public/favicons');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $filename = 'favicon-' . time() . '.png';
        imagepng($circle, $dir . '/' . $filename, 9);
        imagedestroy($circle);

        return 'favicons/' . $filename;
    }
}
