<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Redirige accesos al portal legacy (homedelvalle.mx/portal/*)
 * al subdominio dedicado (miportal.homedelvalle.mx/*).
 *
 * Sólo activo en producción. En local dev se omite para no romper tests.
 */
class PortalRedirectLegacy
{
    public function handle(Request $request, Closure $next): Response
    {
        // Solo redirigir si es el dominio principal (no el subdominio)
        $host = $request->getHost();

        if ($host === 'homedelvalle.mx' && str_starts_with($request->getPathInfo(), '/portal')) {
            $path = ltrim(str_replace('/portal', '', $request->getPathInfo()), '/');
            $path = $path ?: 'inicio';

            $query = $request->getQueryString() ? '?' . $request->getQueryString() : '';

            return redirect()->to(
                "https://miportal.homedelvalle.mx/{$path}{$query}",
                301
            );
        }

        return $next($request);
    }
}
