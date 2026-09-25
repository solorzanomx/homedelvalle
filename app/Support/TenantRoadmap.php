<?php

namespace App\Support;

use App\Models\Document;
use App\Models\PolizaJuridica;
use App\Models\PolizaPlan;
use App\Models\RentalProcess;

/**
 * "¿Qué sigue?" del INQUILINO (2026-09-26): el camino completo de su renta con el
 * estado real de cada paso, para que sepa siempre dónde está y qué le toca.
 *
 *   1 Apartado → 2 Documentos → 3 Garantía → 4 Contrato → 5 Entrega y renta
 *
 * La GARANTÍA depende de la ruta:
 *   póliza  → el inquilino no tiene aval con inmueble en CDMX (o el asesor la fijó):
 *             elige uno de los planes (catálogo PolizaPlan); Previsión Legal hace
 *             su propia investigación; paga DIRECTO al proveedor.
 *   aval    → investigación de Home del Valle (cuota de $3,500 MXN, no reembolsable)
 *             + datos y documentos del aval; el propietario aprueba al candidato.
 *   indefinida → todavía no declara; el Portal le pregunta.
 *
 * Solo LEE estado; los cambios los hacen PortalRentalController y el CRM.
 */
class TenantRoadmap
{
    const INVESTIGATION_FEE = 3500;

    const ROUTE_POLIZA = 'poliza';
    const ROUTE_AVAL = 'aval';
    const ROUTE_UNDECIDED = 'undecided';

    /** Ruta de garantía: la declaración del inquilino manda; si no, lo que fijó el asesor. */
    public static function route(RentalProcess $r): string
    {
        if ($r->tenant_has_aval === false) {
            return self::ROUTE_POLIZA; // sin aval en CDMX: es póliza, definitivamente
        }
        if ($r->tenant_has_aval === true) {
            return self::ROUTE_AVAL;
        }

        return match ($r->guarantee_type) {
            'poliza_juridica' => self::ROUTE_POLIZA,
            'aval', 'aval_pagares', 'pagares' => self::ROUTE_AVAL,
            default => self::ROUTE_UNDECIDED, // 'deposito' es el default de la columna: no dice nada
        };
    }

    /**
     * @return array{route:string, steps:array<int,array<string,mixed>>, plans:\Illuminate\Support\Collection, current:?string}
     */
    public static function build(RentalProcess $r): array
    {
        $r->loadMissing(['documents', 'contracts', 'poliza', 'investigation', 'polizaPlan']);
        $route = self::route($r);
        $plans = PolizaPlan::offered()->get();

        $steps = [
            self::apartado($r),
            self::informacion($r),
            self::documentos($r),
            self::garantia($r, $route, $plans),
            self::contrato($r, $route),
            self::entrega($r),
        ];

        // El primer paso NO terminado (salvo el apartado, que va en paralelo) es el activo; los demás quedan pendientes.
        $current = null;
        foreach ($steps as &$s) {
            if ($s['done']) {
                $s['state'] = 'done';
            } elseif (! empty($s['parallel'])) {
                // El apartado NO bloquea el resto (decisión 2026-09-24): se puede avanzar con documentos y garantía mientras tanto.
                $s['state'] = 'pending';
            } elseif ($current === null) {
                $s['state'] = 'active';
                $current = $s['key'];
            } else {
                $s['state'] = 'todo';
            }
        }
        unset($s);

        return ['route' => $route, 'steps' => $steps, 'plans' => $plans, 'current' => $current];
    }

    /**
     * "Tu siguiente paso": UNA acción concreta para el inquilino (título, texto, tiempo estimado y botón).
     * Sale del paso activo del camino. `cta_url` null = no hay nada que hacer ahora (esperando al asesor).
     *
     * @param  array  $rm  el resultado de build()
     * @return array{title:string, body:string, minutes:?int, cta_label:?string, cta_url:?string, step:?string, secondary:?array}
     */
    public static function nextAction(RentalProcess $r, array $rm): array
    {
        $steps = collect($rm['steps'])->keyBy('key');
        $step = $rm['current'];
        $docs = route('portal.documents.index');

        $secondary = null;
        if (! $steps['apartado']['done']) {
            $secondary = ['title' => 'Aparta tu inmueble', 'body' => 'Tu depósito reserva tu lugar mientras avanzas con lo demás.', 'cta_label' => 'Cómo apartar', 'cta_url' => route('portal.expediente') . '#apartado'];
        }

        $a = match ($step) {
            'informacion' => ['title' => 'Completa tus datos', 'body' => 'Datos personales, domicilio, trabajo y referencias. Se guardan mientras escribes.',
                'minutes' => 5, 'cta_label' => 'Completar mis datos', 'cta_url' => route('portal.expediente')],

            'documentos' => self::nextForDocuments($steps['documentos'], $docs),

            'garantia' => match (true) {
                ($steps['garantia']['action'] ?? null) === 'declare' => ['title' => 'Define tu garantía', 'body' => '¿Tienes un aval con propiedad en CDMX? Con tu respuesta sabemos qué sigue.',
                    'minutes' => 1, 'cta_label' => 'Responder', 'cta_url' => '#step-garantia'],
                ($steps['garantia']['action'] ?? null) === 'choose_plan' => ['title' => 'Elige tu plan de póliza', 'body' => 'Compara los planes y elige el que mejor te acomode. La póliza la pagas directo a Previsión Legal.',
                    'minutes' => 2, 'cta_label' => 'Ver los planes', 'cta_url' => '#step-garantia'],
                ($steps['garantia']['route'] ?? null) === self::ROUTE_AVAL && ! ($steps['garantia']['fee_paid'] ?? false) => ['title' => 'Completa los datos de tu aval', 'body' => 'Datos y documentos de tu aval; tu asesor confirmará la cuota de investigación ($' . number_format(self::INVESTIGATION_FEE) . ').',
                    'minutes' => 10, 'cta_label' => 'Ver documentos del aval', 'cta_url' => $docs],
                default => ['title' => 'Estamos trabajando en tu garantía', 'body' => $steps['garantia']['summary'], 'minutes' => null, 'cta_label' => null, 'cta_url' => null],
            },

            'contrato' => ! empty($steps['contrato']['contract']) && $steps['contrato']['contract']->pdf_path
                ? ['title' => 'Revisa y firma tu contrato', 'body' => 'Léelo con calma; si tienes dudas, tu asesor te acompaña.', 'minutes' => 10,
                    'cta_label' => 'Ver mi contrato', 'cta_url' => route('contracts.download', $steps['contrato']['contract']->id)]
                : ['title' => 'Tu contrato está en preparación', 'body' => $steps['contrato']['summary'], 'minutes' => null, 'cta_label' => null, 'cta_url' => null],

            'entrega' => ['title' => 'Casi listo: la entrega de tu inmueble', 'body' => $steps['entrega']['summary'], 'minutes' => null, 'cta_label' => null, 'cta_url' => null],

            default => ['title' => '¡Todo en orden!', 'body' => 'Completaste todos los pasos. Tu asesor te contactará ante cualquier novedad.', 'minutes' => null, 'cta_label' => null, 'cta_url' => null],
        };

        return $a + ['step' => $step, 'secondary' => $secondary];
    }

    private static function nextForDocuments(array $step, string $docsUrl): array
    {
        $c = $step['counts'];
        if ($c['rejected'] > 0) {
            return ['title' => 'Corrige ' . $c['rejected'] . ($c['rejected'] === 1 ? ' documento' : ' documentos'), 'body' => 'Tu asesor dejó el motivo en cada uno. Súbelos de nuevo y listo.',
                'minutes' => 3, 'cta_label' => 'Ver qué corregir', 'cta_url' => $docsUrl];
        }
        if (! empty($step['missing'])) {
            return ['title' => 'Sube tu ' . mb_strtolower($step['missing'][0]), 'body' => 'Faltan: ' . implode(', ', $step['missing']) . '. Puedes tomarle foto o subir el PDF.',
                'minutes' => 2, 'cta_label' => 'Subir documentos', 'cta_url' => $docsUrl];
        }

        return ['title' => 'Tu asesor está revisando tus documentos', 'body' => 'No tienes nada pendiente por ahora. Te avisamos en cuanto estén aprobados.', 'minutes' => null, 'cta_label' => null, 'cta_url' => null];
    }

    private static function apartado(RentalProcess $r): array
    {
        $done = (bool) $r->apartado_paid_at;

        return [
            'key' => 'apartado', 'title' => 'Apartado', 'done' => $done, 'parallel' => true,
            'summary' => $done
                ? 'Tu apartado quedó confirmado el ' . $r->apartado_paid_at->format('d/m/Y') . '.'
                : 'Aparta el inmueble con tu depósito para reservar tu lugar.',
        ];
    }

    /** "Tus datos": la información del expediente (datos personales, domicilio, trabajo, referencias). */
    private static function informacion(RentalProcess $r): array
    {
        $pct = (int) ($r->tenantClient?->legal_completeness ?? 0);
        $done = $pct >= 100;

        return ['key' => 'informacion', 'title' => 'Tus datos', 'done' => $done, 'pct' => $pct,
            'summary' => $done
                ? 'Tu información está completa. ✅'
                : "Completa tus datos personales, domicilio, trabajo y referencias (llevas {$pct}%). Se guardan mientras avanzas."];
    }

    private static function documentos(RentalProcess $r): array
    {
        $tenantDocs = $r->documents->filter(fn(Document $d) => ! $d->client_id || $d->client_id === $r->tenant_client_id);
        $approved = $tenantDocs->where('status', 'verified')->count();
        $review = $tenantDocs->where('status', 'received')->count();
        $rejected = $tenantDocs->where('status', 'rejected')->count();
        $done = RentalExpedienteStatus::isComplete($r);

        if ($done) {
            $summary = 'Todos tus documentos fueron revisados y aprobados. ✅';
        } elseif ($rejected) {
            $summary = "Tienes {$rejected} documento(s) por corregir: revisa el motivo y vuelve a subirlos.";
        } elseif ($review) {
            $summary = "Tu asesor está revisando {$review} documento(s). Falta: " . implode(', ', RentalExpedienteStatus::missing($r)) . '.';
        } else {
            $summary = 'Sube tu identificación, comprobante de domicilio y comprobante de ingresos. Falta: ' . implode(', ', RentalExpedienteStatus::missing($r)) . '.';
        }

        return ['key' => 'documentos', 'title' => 'Tus documentos', 'done' => $done, 'summary' => $summary,
            'counts' => compact('approved', 'review', 'rejected'), 'missing' => $done ? [] : RentalExpedienteStatus::missing($r)];
    }

    private static function garantia(RentalProcess $r, string $route, $plans): array
    {
        $base = ['key' => 'garantia', 'title' => 'Garantía de tu renta', 'route' => $route];

        if ($route === self::ROUTE_UNDECIDED) {
            return $base + ['done' => false, 'action' => 'declare',
                'summary' => 'Cuéntanos cómo respaldarás tu renta: con un aval que tenga propiedad en CDMX, o con una póliza jurídica.'];
        }

        if ($route === self::ROUTE_POLIZA) {
            $plan = $r->polizaPlan;
            $poliza = $r->poliza;
            if (! $plan) {
                return $base + ['done' => false, 'action' => $plans->isEmpty() ? null : 'choose_plan',
                    'summary' => 'Sin aval en CDMX, tu garantía es una póliza jurídica con Previsión Legal, quien realiza la investigación. Elige el plan que mejor te acomode.'];
            }
            $approved = $poliza && $poliza->status === 'approved';
            $status = $poliza ? (PolizaJuridica::STATUSES[$poliza->status] ?? $poliza->status) : 'Pendiente';

            return $base + ['done' => $approved, 'plan' => $plan, 'poliza' => $poliza,
                'summary' => $approved
                    ? "Tu póliza {$plan->name} fue aprobada. ✅"
                    : "Elegiste el plan {$plan->name} ({$plan->price_formatted}). Estado: {$status}. Tu asesor coordina el alta con Previsión Legal; el pago de la póliza lo haces directo con ellos.",
            ];
        }

        // Ruta aval: investigación de Home del Valle
        $inv = $r->investigation;
        $paid = (bool) $r->investigacion_paid_at;
        $decision = $inv?->owner_decision;
        $done = $decision === 'approved';

        if ($done) {
            $summary = 'Tu perfil y tu aval fueron aprobados. ✅';
        } elseif ($decision === 'declined') {
            $summary = 'El propietario no aprobó este perfil. Tu asesor te contactará para ver alternativas (por ejemplo, una póliza jurídica).';
        } elseif (! $paid) {
            $summary = 'Con aval, hacemos la investigación (cuota de $' . number_format(self::INVESTIGATION_FEE) . ' MXN, no reembolsable). Completa los datos y documentos de tu aval y confirma el pago con tu asesor.';
        } else {
            $summary = 'Cuota recibida. Estamos investigando tu perfil y tu aval; en cuanto termine, se lo presentamos al propietario.';
        }

        return $base + ['done' => $done, 'fee' => self::INVESTIGATION_FEE, 'fee_paid' => $paid, 'summary' => $summary];
    }

    private static function contrato(RentalProcess $r, string $route): array
    {
        $contract = $r->contracts->sortByDesc('created_at')->first();
        $signed = $contract && $contract->signature_status === 'signed';

        if ($contract) {
            $summary = $signed
                ? 'Tu contrato está firmado. ✅'
                : ($route === self::ROUTE_POLIZA
                    ? 'Tu contrato de arrendamiento (emitido por Previsión Legal) ya está disponible: revísalo y fírmalo.'
                    : 'Tu contrato ya está listo: revísalo y fírmalo.');
        } else {
            $summary = $route === self::ROUTE_POLIZA
                ? 'Cuando tu póliza esté aprobada, Previsión Legal emite tu contrato de arrendamiento; tu asesor lo sube aquí para que lo revises y firmes.'
                : 'Cuando se apruebe tu perfil, tu asesor prepara tu contrato de arrendamiento y lo verás aquí para revisarlo y firmarlo.';
        }

        return ['key' => 'contrato', 'title' => 'Contrato y firma', 'done' => $signed, 'contract' => $contract, 'summary' => $summary];
    }

    private static function entrega(RentalProcess $r): array
    {
        $done = in_array($r->stage, ['entrega', 'activo', 'renovacion', 'cerrado'], true);

        return ['key' => 'entrega', 'title' => 'Entrega e inicio de tu renta', 'done' => $done,
            'summary' => $done
                ? 'Tu renta está en marcha. Aquí verás tus pagos y fechas importantes.'
                : 'Con el contrato firmado, coordinamos la entrega del inmueble y el inicio de tu renta.'];
    }
}
