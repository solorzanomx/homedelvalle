<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckViewerRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // SEGURIDAD: 'user' (el rol que asignaba el registro público) NO da
        // acceso al CRM — incidente real 2026-07-17: un desconocido se
        // registró por /register y ese rol pasaba esta puerta (148 rutas
        // admin, incluidos leads con datos personales). El registro público
        // quedó deshabilitado; los usuarios los crea un admin en Gestión de
        // usuarios.
        if ($request->user() && in_array($request->user()->role, ['admin', 'editor', 'viewer', 'broker'])) {
            return $next($request);
        }

        abort(403, 'Unauthorized - Viewer access required');
    }
}
