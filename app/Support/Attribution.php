<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Atribución de primer contacto: qué página trajo al visitante al sitio y
 * con qué UTM, guardada en sesión la primera vez que se detecta (no se
 * sobreescribe en visitas posteriores dentro de la misma sesión). Se captura
 * en App\Http\Middleware\CaptureLandingAttribution y se consume en
 * App\Models\Concerns\HasAttribution al crear un lead/conversión.
 */
class Attribution
{
    private const SESSION_KEY = 'attribution';

    // Prefijos/nombres de ruta que no cuentan como "página de entrada"
    // (admin, auth, assets, llamadas internas de Livewire).
    private const EXCLUDED_ROUTE_PREFIXES = ['admin.', 'livewire.'];
    private const EXCLUDED_ROUTE_NAMES = ['login', 'logout', 'password.request', 'password.email', 'password.reset', 'password.update'];

    public static function capture(Request $request): void
    {
        if ($request->session()->has(self::SESSION_KEY)) {
            return; // ya se capturó el primer contacto de esta sesión
        }

        if (!$request->isMethod('GET') || !$request->route()) {
            return;
        }

        $routeName = $request->route()->getName();

        if ($routeName === null || in_array($routeName, self::EXCLUDED_ROUTE_NAMES, true)) {
            return;
        }
        foreach (self::EXCLUDED_ROUTE_PREFIXES as $prefix) {
            if (str_starts_with($routeName, $prefix)) {
                return;
            }
        }

        $request->session()->put(self::SESSION_KEY, [
            'landing_url'     => $request->fullUrl(),
            'landing_label'   => AttributionLabeler::label($request),
            'landing_post_id' => AttributionLabeler::postIdFromRoute($request),
            'utm_source'      => $request->query('utm_source'),
            'utm_medium'      => $request->query('utm_medium'),
            'utm_campaign'    => $request->query('utm_campaign'),
            'referrer'        => $request->headers->get('referer'),
            'landed_at'       => now()->toDateTimeString(),
        ]);
    }

    /** Campos listos para hacer fallback en un modelo de conversión (todos null si no hay atribución en sesión). */
    public static function fields(): array
    {
        $data = session(self::SESSION_KEY, []);

        return [
            'landing_post_id' => $data['landing_post_id'] ?? null,
            'landing_label'   => $data['landing_label'] ?? null,
            'utm_source'      => $data['utm_source'] ?? null,
            'utm_medium'      => $data['utm_medium'] ?? null,
            'utm_campaign'    => $data['utm_campaign'] ?? null,
            'referrer'        => $data['referrer'] ?? null,
        ];
    }
}
