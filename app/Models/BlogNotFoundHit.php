<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlogNotFoundHit extends Model
{
    protected $fillable = ['slug', 'hits', 'last_hit_at'];

    protected function casts(): array
    {
        return ['last_hit_at' => 'datetime'];
    }

    public static function register(string $slug): void
    {
        $hit = static::firstOrNew(['slug' => $slug]);
        $hit->hits = ($hit->hits ?? 0) + 1;
        $hit->last_hit_at = now();
        $hit->save();
    }
}
