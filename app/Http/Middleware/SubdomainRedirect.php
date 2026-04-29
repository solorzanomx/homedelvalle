<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SubdomainRedirect
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Si accede a admin.homedelvalle.mx
        if (str_contains($request->getHost(), 'admin.homedelvalle.mx')) {
            $path = $request->path();

            // Si NO está en /admin, /login, /forgot-password, /reset-password o /register
            $allowedPaths = ['/login', '/forgot-password', '/reset-password', '/register'];
            $isAllowed = in_array($path, $allowedPaths) || str_starts_with($path, '/admin');

            // Redirige a /admin solo si no está en rutas permitidas
            if (!$isAllowed) {
                return redirect('/admin');
            }
        }

        return $next($request);
    }
}
