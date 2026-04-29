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
        // Si accede a admin.homedelvalle.mx, redirige a /admin
        if ($request->getHost() === 'admin.homedelvalle.mx' && !str_starts_with($request->path(), '/admin')) {
            return redirect('/admin');
        }

        return $next($request);
    }
}
