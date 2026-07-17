<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Separa el CRM del sitio público por dominio (pedido de Alejandro
 * 2026-07-17): el CRM vive en admin.homedelvalle.mx y el sitio en
 * homedelvalle.mx — antes ambos servían todo, duplicando el sitio público
 * en el subdominio (SEO) y mezclando sesiones de admin con navegación
 * pública.
 *
 * Reglas:
 * - Ruta CRM (requiere auth, o es login/recuperación) visitada en el
 *   dominio público → 302 al subdominio admin (misma ruta y query). Cubre
 *   también los links de correos/notificaciones generados por CLI con
 *   APP_URL.
 * - Ruta pública visitada en el subdominio admin → 301 al dominio público.
 * - El portal (miportal.*) tiene sus rutas atadas a su dominio — aquí se
 *   ignora por completo.
 * - Livewire y API quedan fuera (operan en ambos hosts).
 * - Los links públicos que se envían a clientes (confirmar visita, firma,
 *   presentaciones) no requieren auth → viven en el dominio público, como
 *   debe ser.
 * - Solo se redirigen GET/HEAD — un POST al host equivocado se sirve
 *   normal antes que perder el cuerpo del formulario.
 * - Sin ADMIN_DOMAIN configurado (local/dev) es un no-op total.
 */
class SeparateAdminHost
{
    /** Rutas de invitado que pertenecen al CRM (viven en el subdominio). */
    private const GUEST_CRM_ROUTES = [
        'login', 'password.forgot', 'password.reset',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $adminHost = config('app.admin_domain');
        if (! $adminHost) {
            return $next($request);
        }

        $host = $request->getHost();

        // Portal y hosts ajenos: no intervenir
        if ($host !== $adminHost && ! in_array($host, [parse_url(config('app.url'), PHP_URL_HOST), 'www.' . parse_url(config('app.url'), PHP_URL_HOST)])) {
            return $next($request);
        }

        if ($request->is('livewire/*') || $request->is('api/*')) {
            return $this->tagAdmin($next($request), $host === $adminHost);
        }

        // La raíz del subdominio es la puerta del CRM: a /admin (que a su vez
        // manda al login si no hay sesión). Sin esto, escribir
        // admin.homedelvalle.mx a secas rebotaba al sitio público y nunca
        // veías el login (bug real reportado 2026-07-17).
        if ($host === $adminHost && $request->path() === '/') {
            return redirect()->to('https://' . $adminHost . '/admin', 302);
        }

        $route = $request->route();
        $esCrm = $route && (
            in_array('auth', $route->gatherMiddleware(), true)
            || in_array($route->getName(), self::GUEST_CRM_ROUTES, true)
        );

        $publicHost = parse_url(config('app.url'), PHP_URL_HOST) ?: 'homedelvalle.mx';

        if ($request->isMethod('GET') || $request->isMethod('HEAD')) {
            if ($esCrm && $host !== $adminHost) {
                return redirect()->to('https://' . $adminHost . $request->getRequestUri(), 302);
            }
            if (! $esCrm && $host === $adminHost) {
                return redirect()->to('https://' . $publicHost . $request->getRequestUri(), 301);
            }
        }

        return $this->tagAdmin($next($request), $host === $adminHost);
    }

    /** El subdominio admin jamás se indexa. */
    private function tagAdmin(Response $response, bool $esAdminHost): Response
    {
        if ($esAdminHost) {
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
        }

        return $response;
    }
}
