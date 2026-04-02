<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HomepageController extends Controller
{
    public function index()
    {
        $settings = SiteSetting::first();
        return view('admin.homepage', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            // Hero
            'hero_heading' => 'nullable|string|max:255',
            'hero_subheading' => 'nullable|string|max:500',
            'hero_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',

            // Benefits
            'benefits_heading' => 'nullable|string|max:255',
            'benefits_subheading' => 'nullable|string|max:500',
            'benefits_section' => 'nullable|array|max:4',
            'benefits_section.*.icon' => 'nullable|string|max:50',
            'benefits_section.*.title' => 'nullable|string|max:100',
            'benefits_section.*.description' => 'nullable|string|max:300',

            // Featured properties
            'featured_heading' => 'nullable|string|max:255',
            'featured_subheading' => 'nullable|string|max:500',

            // Services
            'services_heading' => 'nullable|string|max:255',
            'services_subheading' => 'nullable|string|max:500',
            'services_section' => 'nullable|array|max:3',
            'services_section.*.title' => 'nullable|string|max:100',
            'services_section.*.description' => 'nullable|string|max:500',
            'services_section.*.features' => 'nullable|array|max:3',
            'services_section.*.features.*' => 'nullable|string|max:100',
            'services_section.*.link_text' => 'nullable|string|max:100',
            'services_section.*.link_url' => 'nullable|string|max:255',
            'services_section.*.highlighted' => 'nullable',

            // Testimonials
            'testimonials_heading' => 'nullable|string|max:255',
            'testimonials_subheading' => 'nullable|string|max:500',
            'testimonials_section' => 'nullable|array|max:3',
            'testimonials_section.*.name' => 'nullable|string|max:100',
            'testimonials_section.*.role' => 'nullable|string|max:100',
            'testimonials_section.*.text' => 'nullable|string|max:500',
            'testimonials_section.*.initials' => 'nullable|string|max:4',

            // Contact
            'contact_heading' => 'nullable|string|max:255',
            'contact_subheading' => 'nullable|string|max:500',
            'contact_phone' => 'nullable|string|max:50',
            'contact_email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'whatsapp_number' => 'nullable|string|max:50',
            'facebook_url' => 'nullable|url|max:255',
            'instagram_url' => 'nullable|url|max:255',
            'tiktok_url' => 'nullable|url|max:255',

            // Blog
            'blog_heading' => 'nullable|string|max:255',
            'blog_subheading' => 'nullable|string|max:500',

            // CTA Final
            'cta_heading' => 'nullable|string|max:255',
            'cta_subheading' => 'nullable|string|max:500',

            // Templates
            'property_listing_template' => 'nullable|string|in:grid,list,magazine',
            'property_detail_template' => 'nullable|string|in:sidebar,fullwidth,gallery',
            'blog_template' => 'nullable|string|in:grid,list,magazine',
        ]);

        $settings = SiteSetting::first();

        // Handle hero image upload
        if ($request->hasFile('hero_image')) {
            if ($settings && $settings->hero_image_path) {
                Storage::disk('public')->delete($settings->hero_image_path);
            }
            $validated['hero_image_path'] = $request->file('hero_image')->store('heroes', 'public');
        }
        unset($validated['hero_image']);

        // Normalize highlighted checkbox to boolean
        if (isset($validated['services_section'])) {
            foreach ($validated['services_section'] as &$service) {
                $service['highlighted'] = isset($service['highlighted']);
                $service['features'] = array_values(array_filter($service['features'] ?? []));
            }
            unset($service);
        }

        // Filter out empty benefit items
        if (isset($validated['benefits_section'])) {
            $validated['benefits_section'] = array_values(array_filter($validated['benefits_section'], fn($b) => !empty($b['title'])));
        }

        // Filter out empty testimonial items
        if (isset($validated['testimonials_section'])) {
            $validated['testimonials_section'] = array_values(array_filter($validated['testimonials_section'], fn($t) => !empty($t['name'])));
        }

        if ($settings) {
            $settings->update($validated);
        } else {
            SiteSetting::create($validated);
        }

        cache()->forget('site_settings');

        return back()->with('success', 'Homepage actualizado correctamente.');
    }
}
