<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadScoreRule extends Model
{
    protected $fillable = ['event', 'points', 'description', 'is_active', 'max_per_day'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function scopeActive($q) { return $q->where('is_active', true); }
}
