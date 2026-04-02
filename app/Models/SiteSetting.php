<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'site_name', 'site_tagline', 'primary_color', 'secondary_color', 'home_welcome_text', 'logo_path', 'logo_type',
    'whatsapp_number', 'contact_email', 'contact_phone', 'address',
    'facebook_url', 'instagram_url', 'tiktok_url',
    'about_text', 'google_maps_embed',
    'hero_image_path', 'hero_heading', 'hero_subheading',
    'benefits_section', 'services_section', 'testimonials_section',
    'benefits_heading', 'benefits_subheading',
    'services_heading', 'services_subheading',
    'testimonials_heading', 'testimonials_subheading',
    'featured_heading', 'featured_subheading',
    'blog_heading', 'blog_subheading',
    'cta_heading', 'cta_subheading',
    'contact_heading', 'contact_subheading',
    'property_listing_template', 'property_detail_template', 'blog_template',
    'footer_about', 'footer_bottom_text', 'footer_bottom_links',
])]
class SiteSetting extends Model
{
    protected $casts = [
        'benefits_section' => 'array',
        'services_section' => 'array',
        'testimonials_section' => 'array',
        'footer_bottom_links' => 'array',
    ];
    public static function current(): ?self
    {
        $data = cache()->remember('site_settings', 300, function () {
            return static::first()?->toArray();
        });

        if (! $data) {
            return null;
        }

        return (new static)->forceFill($data);
    }
}

