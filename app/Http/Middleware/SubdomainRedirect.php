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
        // Si accede a admin.homedelvalle.mx pero NO está ya en /admin
        // Redirige a /admin para que todo el tráfico del subdominio vaya al panel
        if (str_contains($request->getHost(), 'admin.homedelvalle.mx')) {
            $path = $request->path();

            // Si ya está en /admin, dejar pasar
            if (str_starts_with($path, '/admin')) {
                return $next($request);
            }

            // Si está intentando acceder a rutas de autenticación, dejar pasar
            $authPaths = ['/login', '/forgot-password', '/reset-password', '/register'];
            if (in_array($path, $authPaths)) {
                return $next($request);
            }

            // Si está en raíz o cualquier otra ruta, redirige a /admin
            if ($path === '/' || $path === '') {
                return redirect('/admin');
            }
        }

        return $next($request);
    }
}
