<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CarouselAsset extends Model
{
    protected $fillable = [
        'carousel_post_id',
        'slide_id',
        'type',
        'source',
        'path',
        'mime_type',
        'width',
        'height',
        'alt_text',
    ];

    protected function casts(): array
    {
        return [
            'width'  => 'integer',
            'height' => 'integer',
        ];
    }

    public function carouselPost(): BelongsTo
    {
        return $this->belongsTo(CarouselPost::class);
    }

    public function slide(): BelongsTo
    {
        return $this->belongsTo(CarouselSlide::class, 'slide_id');
    }
}
