<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(403, 'No autenticado');
        }

        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        if (!$user->hasAnyPermission(...$permissions)) {
            abort(403, 'No tienes permiso para acceder a este recurso');
        }

        return $next($request);
    }
}
