<?php

namespace App\Http\Middleware;

use App\Models\BlogRedirect;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Salud de URLs del blog (docs/funcionalidades/blog-redirects.md).
 * Global (corre para CUALQUIER request, tenga o no ruta): un slug con mayúsculas o barra final
 * NUNCA llega a hacer match con `blog/{slug}` (Laravel ni siquiera lo intenta), así que la
 * normalización tiene que pasar ANTES del enrutamiento — de ahí que viva aquí y no en el controlador.
 */
class BlogUrlHealth
{
    public function handle(Request $request, Closure $next): Response
    {
        $path = $request->path();   // sin query string, sin barra inicial

        if (! str_starts_with($path, 'blog/') || $path === 'blog/') {
            return $next($request);
        }

        $rawSlug = substr($path, strlen('blog/'));
        $hadTrailingSlash = str_ends_with($rawSlug, '/');
        $slug = strtolower(rtrim($rawSlug, '/'));
        $normalizedPath = '/blog/' . $slug;

        // 1) ¿Hay un redirect explícito (seed, fusión, auto por slug cambiado/similitud)?
        $redirect = BlogRedirect::active()->where('from_path', $normalizedPath)->first();
        if ($redirect) {
            $redirect->registerHit();

            if ((int) $redirect->status === 410) {
                return response()->view('blog.gone', ['to' => $redirect->to_path], 410);
            }

            return redirect($redirect->to_path, 301);
        }

        // 2) Sin redirect, pero la URL no viene normalizada (mayúsculas o barra final) → 301 a la forma canónica.
        if ($hadTrailingSlash || $rawSlug !== $slug) {
            $qs = $request->getQueryString();

            return redirect($normalizedPath . ($qs ? '?' . $qs : ''), 301);
        }

        return $next($request);
    }
}
