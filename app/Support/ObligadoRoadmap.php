<?php

namespace App\Support;

use App\Models\Client;
use App\Models\RentalProcess;
use App\Services\ObligadoSolidarioService;

/**
 * "Mi camino" del OBLIGADO SOLIDARIO (2026-09-28): tres pasos simples — sus datos, sus documentos y la revisión del
 * asesor. Misma forma que TenantRoadmap para reutilizar la vista (`portal/journey`, `_tenant_roadmap`).
 */
class ObligadoRoadmap
{
    /** @return array{route:?string, steps:array<int,array<string,mixed>>, plans:\Illuminate\Support\Collection, current:?string} */
    public static function build(RentalProcess $rental, Client $os): array
    {
        $svc = app(ObligadoSolidarioService::class);
        $pct = $svc->dataProgress($os);
        $rows = TenantDocumentRows::build($rental, $os, null, true);
        $c = $rows['counts'];
        $docsDelivered = $c['falta'] === 0 && $c['corregir'] === 0;
        $complete = $svc->status($rental)['complete'];

        $steps = [
            ['key' => 'informacion', 'title' => 'Tus datos', 'done' => $pct >= 100, 'pct' => $pct,
                'summary' => $pct >= 100 ? 'Tu información está completa. ✅' : "Datos personales, identificación y domicilio, y trabajo e ingresos (llevas {$pct}%). Se guardan mientras avanzas."],
            ['key' => 'documentos', 'title' => 'Tus documentos', 'done' => $docsDelivered, 'counts' => $c, 'missing' => [],
                'summary' => $docsDelivered
                    ? 'Ya subiste todo lo que pedimos. ✅'
                    : ($c['corregir'] > 0 ? "Tienes {$c['corregir']} documento(s) por corregir." : "Sube tu identificación, tu comprobante de domicilio y tus comprobantes de ingresos de los últimos 3 meses. Faltan {$c['falta']}.")],
            ['key' => 'revision', 'title' => 'Revisión de tu asesor', 'done' => $complete,
                'summary' => $complete ? 'Tu asesor aprobó tu información. ¡Gracias por tu apoyo! ✅' : 'Tu asesor revisa lo que subiste y te avisa si necesita algo más.'],
        ];

        $current = null;
        foreach ($steps as &$s) {
            if ($s['done']) {
                $s['state'] = 'done';
            } elseif ($current === null) {
                $s['state'] = 'active';
                $current = $s['key'];
            } else {
                $s['state'] = 'todo';
            }
        }
        unset($s);

        return ['route' => null, 'steps' => $steps, 'plans' => collect(), 'current' => $current];
    }

    /** UNA acción concreta (o "esperando"), igual que TenantRoadmap::nextAction. */
    public static function nextAction(array $rm): array
    {
        $steps = collect($rm['steps'])->keyBy('key');
        $docs = route('portal.documents.index');

        $a = match ($rm['current']) {
            'informacion' => ['title' => 'Completa tus datos', 'body' => 'Datos personales, identificación y domicilio, y trabajo e ingresos. Se guardan mientras escribes.', 'minutes' => 5, 'cta_label' => 'Completar mis datos', 'cta_url' => route('portal.expediente')],
            'documentos' => ($steps['documentos']['counts']['corregir'] ?? 0) > 0
                ? ['title' => 'Corrige ' . $steps['documentos']['counts']['corregir'] . ' documento(s)', 'body' => 'Tu asesor dejó el motivo en cada uno. Súbelos de nuevo y listo.', 'minutes' => 3, 'cta_label' => 'Ver qué corregir', 'cta_url' => $docs]
                : ['title' => 'Sube tus documentos', 'body' => 'INE (frente y vuelta) o pasaporte, comprobante de domicilio y tus comprobantes de ingresos de los últimos 3 meses. Puedes tomarles foto o subir el PDF.', 'minutes' => 6, 'cta_label' => 'Subir documentos', 'cta_url' => $docs],
            'revision' => ['title' => 'Tu asesor está revisando tu información', 'body' => 'No tienes nada pendiente por ahora. Te avisamos si necesita algo más.', 'minutes' => null, 'cta_label' => null, 'cta_url' => null],
            default => ['title' => '¡Listo, gracias por tu apoyo!', 'body' => 'Tu información quedó aprobada. Tu asesor te contactará ante cualquier novedad.', 'minutes' => null, 'cta_label' => null, 'cta_url' => null],
        };

        return $a + ['step' => $rm['current'], 'secondary' => null];
    }
}
