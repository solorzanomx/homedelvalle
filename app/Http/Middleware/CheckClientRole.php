<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckClientRole
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && $request->user()->role === 'client') {
            return $next($request);
        }

        abort(403, 'Acceso no autorizado - Se requiere rol de cliente');
    }
}
