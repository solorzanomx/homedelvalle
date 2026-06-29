<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ColoniaPage extends Model
{
    protected $fillable = [
        'slug', 'name', 'meta_title', 'meta_description',
        'heading', 'subheading', 'about',
        'faqs', 'colony_search_terms',
        'is_published', 'sort_order',
    ];

    protected $casts = [
        'faqs'         => 'array',
        'is_published' => 'boolean',
    ];

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Terms used to filter properties from the DB.
     * Returns an array from the comma-separated field, falling back to name.
     */
    public function getSearchTermsArray(): array
    {
        $raw = $this->colony_search_terms ?: $this->name;
        return array_filter(array_map('trim', explode(',', $raw)));
    }
}
