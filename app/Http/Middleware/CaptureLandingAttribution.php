<?php

namespace App\Http\Middleware;

use App\Support\Attribution;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Captura la página de entrada (primer contacto) de cada sesión de visitante,
 * para poder atribuir después de dónde vino un lead — ver App\Support\Attribution.
 */
class CaptureLandingAttribution
{
    public function handle(Request $request, Closure $next): Response
    {
        Attribution::capture($request);

        return $next($request);
    }
}
