<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = ['site_name', 'site_tagline', 'primary_color', 'secondary_color', 'home_welcome_text', 'logo_path', 'logo_type', 'whatsapp_number', 'contact_email', 'contact_phone', 'address', 'facebook_url', 'instagram_url', 'tiktok_url', 'about_text', 'google_maps_embed', 'hero_image_path', 'hero_heading', 'hero_subheading', 'benefits_section', 'services_section', 'testimonials_section', 'benefits_heading', 'benefits_subheading', 'services_heading', 'services_subheading', 'testimonials_heading', 'testimonials_subheading', 'featured_heading', 'featured_subheading', 'blog_heading', 'blog_subheading', 'cta_heading', 'cta_subheading', 'contact_heading', 'contact_subheading', 'property_listing_template', 'property_detail_template', 'blog_template', 'footer_about', 'footer_bottom_text', 'footer_bottom_links', 'gtm_enabled', 'gtm_id', 'ga_enabled', 'google_analytics_id', 'fb_pixel_enabled', 'facebook_pixel_id', 'custom_head_scripts', 'custom_body_scripts', 'hero_badge', 'hero_cta_text', 'hero_cta_url', 'hero_secondary_cta_text', 'hero_secondary_cta_url', 'business_model_heading', 'business_model_subheading', 'business_model_content', 'business_model_steps', 'stats_heading', 'stats_subheading', 'stats_section', 'servicios_content', 'nosotros_content', 'vender_content', 'navbar_cta_text', 'navbar_cta_url', 'navbar_cta_enabled',];
    protected $casts = [
        'benefits_section' => 'array',
        'services_section' => 'array',
        'testimonials_section' => 'array',
        'footer_bottom_links' => 'array',
        'gtm_enabled' => 'boolean',
        'ga_enabled' => 'boolean',
        'fb_pixel_enabled' => 'boolean',
        'business_model_steps' => 'array',
        'stats_section' => 'array',
        'servicios_content' => 'array',
        'nosotros_content' => 'array',
        'vender_content' => 'array',
        'navbar_cta_enabled' => 'boolean',
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

