<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HelpTip extends Model
{
    protected $fillable = ['context', 'title', 'content', 'type', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function scopeActive($q)      { return $q->where('is_active', true); }
    public function scopeForContext($q, string $ctx) { return $q->where('context', $ctx)->active()->orderBy('sort_order'); }
}
