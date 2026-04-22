<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CarouselVersion extends Model
{
    protected $fillable = [
        'carousel_post_id',
        'version_number',
        'label',
        'snapshot',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'snapshot'       => 'array',
            'version_number' => 'integer',
        ];
    }

    public function carouselPost(): BelongsTo
    {
        return $this->belongsTo(CarouselPost::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
