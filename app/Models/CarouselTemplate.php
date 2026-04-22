<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CarouselTemplate extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'thumbnail_path',
        'blade_view',
        'canvas_size',
        'default_vars',
        'supported_types',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'default_vars'    => 'array',
            'supported_types' => 'array',
            'is_active'       => 'boolean',
            'sort_order'      => 'integer',
        ];
    }

    public function posts(): HasMany
    {
        return $this->hasMany(CarouselPost::class, 'template_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
