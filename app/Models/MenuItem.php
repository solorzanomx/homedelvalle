<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuItem extends Model
{
    protected $fillable = ['menu_id', 'parent_id', 'label', 'type', 'page_id', 'url', 'route_name', 'target', 'style', 'sort_order', 'is_active'];
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->orderBy('sort_order');
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function resolveUrl(): string
    {
        if ($this->type === 'route' && $this->route_name && \Route::has($this->route_name)) {
            return route($this->route_name);
        }

        if ($this->type === 'page' && $this->page) {
            return route('page.show', $this->page->slug);
        }

        return $this->url ?: '#';
    }
}
