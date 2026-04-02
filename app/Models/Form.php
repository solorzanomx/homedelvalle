<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Form extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'fields', 'settings', 'is_active', 'submissions_count'];
    protected function casts(): array
    {
        return [
            'fields' => 'array',
            'settings' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class);
    }
}
