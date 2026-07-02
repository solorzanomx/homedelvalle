<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;

class PropertyView extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'property_id', 'visitor_key', 'ip_address', 'user_agent', 'referrer', 'viewed_at',
    ];

    protected function casts(): array
    {
        return ['viewed_at' => 'datetime'];
    }

    public function property(): BelongsTo { return $this->belongsTo(Property::class); }

    // Substrings de bots/crawlers conocidos — comparación case-insensitive contra el User-Agent.
    private const BOT_SIGNATURES = [
        'bot', 'spider', 'crawl', 'slurp', 'facebookexternalhit',
        'ahrefsbot', 'semrushbot', 'mj12bot', 'pingdom', 'uptimerobot', 'headlesschrome',
    ];

    // Ventana de de-dup: recargas del mismo visitante a la misma propiedad dentro de
    // este rango no generan una fila nueva, para no inflar "vistas totales".
    private const DEDUP_MINUTES = 30;

    /**
     * Registra una vista de la propiedad, si el visitante no parece un bot y no
     * hay ya una vista reciente del mismo visitante para esa propiedad.
     */
    public static function record(Property $property, Request $request): ?self
    {
        $userAgent = (string) $request->userAgent();

        if ($userAgent === '' || self::looksLikeBot($userAgent)) {
            return null;
        }

        $visitorKey = hash('sha256', $request->session()->getId());

        $recentlyViewed = static::where('property_id', $property->id)
            ->where('visitor_key', $visitorKey)
            ->where('viewed_at', '>=', now()->subMinutes(self::DEDUP_MINUTES))
            ->exists();

        if ($recentlyViewed) {
            return null;
        }

        return static::create([
            'property_id' => $property->id,
            'visitor_key' => $visitorKey,
            'ip_address'  => $request->ip(),
            'user_agent'  => substr($userAgent, 0, 255),
            'referrer'    => $request->headers->get('referer') ? substr($request->headers->get('referer'), 0, 255) : null,
            'viewed_at'   => now(),
        ]);
    }

    private static function looksLikeBot(string $userAgent): bool
    {
        $userAgent = strtolower($userAgent);

        foreach (self::BOT_SIGNATURES as $signature) {
            if (str_contains($userAgent, $signature)) {
                return true;
            }
        }

        return false;
    }
}
