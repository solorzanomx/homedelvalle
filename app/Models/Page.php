<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable = ['title', 'slug', 'body', 'meta_title', 'meta_description', 'sections', 'use_sections', 'is_landing', 'landing_settings', 'is_published', 'sort_order', 'show_in_nav', 'nav_order', 'nav_label', 'nav_url', 'nav_route', 'nav_style'];
    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'show_in_nav' => 'boolean',
            'use_sections' => 'boolean',
            'is_landing' => 'boolean',
            'sections' => 'array',
            'landing_settings' => 'array',
            'sort_order' => 'integer',
            'nav_order' => 'integer',
        ];
    }

    public function scopePublished($q) { return $q->where('is_published', true); }

    public function scopeInNav($q) { return $q->where('show_in_nav', true); }

    /**
     * Get nav items for the public navbar. Cached 5 minutes.
     */
    public static function navItems(): \Illuminate\Support\Collection
    {
        $rows = cache()->remember('nav_items', 300, function () {
            return static::inNav()
                ->published()
                ->orderBy('nav_order')
                ->get(['id', 'title', 'slug', 'nav_label', 'nav_url', 'nav_route', 'nav_style'])
                ->toArray();
        });

        return collect($rows)->map(fn ($arr) => (new static)->forceFill($arr));
    }

    /**
     * Resolve the URL for this nav item.
     */
    public function navHref(): string
    {
        if ($this->nav_route && \Route::has($this->nav_route)) {
            return route($this->nav_route);
        }

        if ($this->nav_url) {
            return $this->nav_url;
        }

        return route('page.show', $this->slug);
    }

    /**
     * Check if this nav item is currently active.
     */
    public function isActive(): bool
    {
        if ($this->nav_route) {
            $base = explode('.', $this->nav_route)[0];
            return request()->routeIs($this->nav_route) || request()->routeIs("{$base}.*");
        }

        if ($this->nav_url) {
            return request()->fullUrlIs(url($this->nav_url));
        }

        return request()->is('p/' . $this->slug);
    }
}
