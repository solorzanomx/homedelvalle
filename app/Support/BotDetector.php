<?php

namespace App\Support;

/**
 * Detección simple de bots/crawlers por substring de User-Agent — usada por
 * cualquier contador de vistas del sitio público (propiedades, blog) para no
 * inflar métricas con tráfico de crawlers conocidos.
 */
class BotDetector
{
    private const SIGNATURES = [
        'bot', 'spider', 'crawl', 'slurp', 'facebookexternalhit',
        'ahrefsbot', 'semrushbot', 'mj12bot', 'pingdom', 'uptimerobot', 'headlesschrome',
    ];

    public static function looksLikeBot(?string $userAgent): bool
    {
        if (!$userAgent) {
            return true;
        }

        $userAgent = strtolower($userAgent);

        foreach (self::SIGNATURES as $signature) {
            if (str_contains($userAgent, $signature)) {
                return true;
            }
        }

        return false;
    }
}
