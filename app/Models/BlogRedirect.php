<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlogRedirect extends Model
{
    protected $fillable = ['from_path', 'to_path', 'status', 'active', 'hits', 'last_hit_at', 'notes'];

    protected function casts(): array
    {
        return ['active' => 'boolean', 'last_hit_at' => 'datetime'];
    }

    public function scopeActive($q)
    {
        return $q->where('active', true);
    }

    /** Normaliza un path de blog para comparar/guardar: minúsculas, sin barra final, siempre con '/blog/' al frente. */
    public static function normalize(string $slugOrPath): string
    {
        $slug = trim($slugOrPath, '/');
        $slug = preg_replace('#^blog/#i', '', $slug);

        return '/blog/' . strtolower(rtrim($slug, '/'));
    }

    public function registerHit(): void
    {
        $this->increment('hits');
        $this->update(['last_hit_at' => now()]);
    }
}
