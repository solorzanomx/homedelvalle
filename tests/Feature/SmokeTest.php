<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

/**
 * Red de seguridad barata: si alguien rompe la sintaxis de un Blade o una
 * ruta, esto lo detecta antes del deploy. Correr siempre antes de hacer push.
 */
class SmokeTest extends TestCase
{
    public function test_all_blade_views_compile(): void
    {
        $code = Artisan::call('view:cache');
        Artisan::call('view:clear');

        $this->assertSame(0, $code, Artisan::output());
    }

    public function test_routes_load(): void
    {
        $this->assertSame(0, Artisan::call('route:list', ['--json' => true]));
    }

    public function test_document_review_routes_exist(): void
    {
        foreach (['documents.preview', 'documents.update-status', 'documents.download', 'documents.destroy'] as $name) {
            $this->assertTrue(\Illuminate\Support\Facades\Route::has($name), "Falta la ruta {$name}");
        }
    }

    /**
     * SEGURIDAD: ninguna ruta autenticada del CRM puede quedar "solo auth". Un usuario del Portal
     * (role 'client') está autenticado y, sin este candado, alcanzaba /clients, /rentals y
     * /documents/{id}/download (incidente detectado 2026-09-26). Toda ruta nueva con `auth` debe
     * llevar además un rol (viewer/admin/broker/client…) o entrar en esta lista con justificación.
     */
    public function test_no_authenticated_route_is_open_to_any_logged_in_user(): void
    {
        $allowed = [
            'logout',                 // cerrar sesión: cualquier usuario autenticado
            'contracts.download',     // el cliente descarga SUS contratos (autoriza el controlador: parte del contrato)
            'portal.preview.exit',    // salir de la vista previa del Portal
            'portal.logout',
        ];
        $roleMiddleware = '/viewer|admin|broker|editor|client$|CheckClientRole|CheckViewerRole|CheckAdminRole|CheckBrokerRole|CheckEditorRole/i';

        $offenders = [];
        foreach (\Illuminate\Support\Facades\Route::getRoutes() as $route) {
            $mw = array_map(fn($m) => is_string($m) ? $m : 'closure', $route->gatherMiddleware());
            if (! in_array(\Illuminate\Auth\Middleware\Authenticate::class, $mw, true) && ! in_array('auth', $mw, true)) {
                continue;
            }
            if (array_filter($mw, fn($m) => preg_match($roleMiddleware, $m))) {
                continue;
            }
            if (! in_array($route->getName(), $allowed, true)) {
                $offenders[] = implode('|', $route->methods()) . ' ' . $route->uri();
            }
        }

        $this->assertSame([], $offenders, "Rutas autenticadas SIN rol (accesibles a un cliente del Portal):\n" . implode("\n", $offenders));
    }
}
