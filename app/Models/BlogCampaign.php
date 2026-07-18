<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BlogCampaign extends Model
{
    protected $fillable = [
        'name', 'objetivo', 'status', 'posts_per_week', 'buffer',
        'publish_hour', 'mezcla', 'lecciones', 'topics', 'started_at',
        'map_requested_at', 'map_requested_count', 'produce_requested_at',
    ];

    protected $casts = [
        'topics'               => 'array',
        'started_at'           => 'date',
        'map_requested_at'     => 'datetime',
        'produce_requested_at' => 'datetime',
    ];

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    /** Borradores generados que esperan el OK del editor. */
    public function draftsPendingReview()
    {
        return $this->posts()->where('status', 'draft')->where('ai_generation_status', 'done');
    }

    public function pendingTopics(): array
    {
        return collect($this->topics ?? [])->where('status', 'pending')->values()->all();
    }

    /** Días entre publicaciones según la cadencia (diaria = 1). */
    public function intervalDays(): int
    {
        return max(1, (int) ceil(7 / max(1, $this->posts_per_week)));
    }

    /**
     * Siguiente fecha de publicación: después del último post programado de
     * la campaña (o desde mañana), respetando la cadencia y la hora fija.
     */
    public function nextPublishDate(): \Carbon\Carbon
    {
        $last = $this->posts()->where('status', 'scheduled')->max('published_at');

        $base = $last
            ? \Carbon\Carbon::parse($last)->addDays($this->intervalDays())
            : now()->addDay();

        [$h, $m] = array_pad(explode(':', $this->publish_hour ?: '08:00'), 2, 0);

        return $base->setTime((int) $h, (int) $m);
    }

    /** Agrega una lección a la bitácora editorial (alimenta prompts futuros). */
    public function addLeccion(string $texto): void
    {
        $this->update(['lecciones' => trim(($this->lecciones ?? '') . "\n- " . trim($texto))]);
    }
}
