<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectToCanonicalHost
{
    /**
     * Consolida el dominio en una sola versión indexable.
     *
     * Google indexaba www.homedelvalle.mx y homedelvalle.mx como dos sitios
     * que compiten entre sí (cada uno con canonical a sí mismo). El sitemap
     * y APP_URL de producción ya usan la versión sin www, así que esa es la
     * canónica: todo lo que llegue por www se redirige 301 conservando ruta
     * y query string.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->getHost() === 'www.homedelvalle.mx') {
            return redirect()->to('https://homedelvalle.mx' . $request->getRequestUri(), 301);
        }

        return $next($request);
    }
}
