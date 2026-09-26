<?php

namespace App\Support;

use App\Models\Document;
use App\Models\PolizaJuridica;
use App\Models\PolizaPlan;
use App\Models\RentalProcess;
use App\Support\PolizaPricing;

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
        $r->loadMissing(['documents', 'contracts', 'poliza', 'investigation', 'polizaPlan', 'obligado']);
        $route = self::route($r);
        $plans = PolizaPlan::offered()->get();

        $obligado = app(\App\Services\ObligadoSolidarioService::class);
        $steps = array_values(array_filter([
            self::apartado($r),
            self::informacion($r),
            self::documentos($r),
            // Sin aval en CDMX (póliza) se pide un obligado solidario con los mismos datos y documentos que el inquilino.
            $obligado->isRequired($r) ? self::obligado($r, $obligado) : null,
            self::garantia($r, $route, $plans),
            self::contrato($r, $route),
            self::entrega($r),
        ]));

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
            $secondary = ['title' => 'Aparta tu inmueble', 'body' => 'Tu depósito reserva tu lugar mientras avanzas con lo demás.', 'cta_label' => 'Cómo apartar', 'cta_url' => route('portal.expediente', ['apartado' => 1]) . '#apartado'];
        }

        $a = match ($step) {
            'informacion' => ['title' => 'Completa tus datos', 'body' => 'Datos personales, domicilio, trabajo y referencias. Se guardan mientras escribes.',
                'minutes' => 5, 'cta_label' => 'Completar mis datos', 'cta_url' => route('portal.expediente')],

            'documentos' => self::nextForDocuments($steps['documentos'], $docs),

            'garantia' => match (true) {
                ($steps['garantia']['action'] ?? null) === 'declare' => ['title' => 'Define tu garantía', 'body' => '¿Tienes un aval con propiedad en CDMX? Con tu respuesta sabemos qué sigue.',
                    'minutes' => 1, 'cta_label' => 'Responder', 'cta_url' => '#step-garantia'],
                ! empty($steps['garantia']['awaiting_owner']) => ['title' => 'Tu propietario está eligiendo tu póliza', 'body' => 'Sin aval en CDMX tu garantía es una póliza jurídica; el dueño elige el plan y cómo se reparte el costo. Te avisamos en cuanto decida.',
                    'minutes' => null, 'cta_label' => null, 'cta_url' => null],
                ($steps['garantia']['route'] ?? null) === self::ROUTE_AVAL && ! ($steps['garantia']['fee_paid'] ?? false) => ['title' => 'Completa los datos de tu aval', 'body' => 'Datos y documentos de tu aval; tu asesor confirmará la cuota de investigación ($' . number_format(self::INVESTIGATION_FEE) . ').',
                    'minutes' => 10, 'cta_label' => 'Ver documentos del aval', 'cta_url' => $docs],
                default => ['title' => 'Estamos trabajando en tu garantía', 'body' => $steps['garantia']['summary'], 'minutes' => null, 'cta_label' => null, 'cta_url' => null],
            },

            'contrato' => ! empty($steps['contrato']['contract']) && $steps['contrato']['contract']->pdf_path
                ? ['title' => 'Revisa y firma tu contrato', 'body' => 'Léelo con calma; si tienes dudas, tu asesor te acompaña.', 'minutes' => 10,
                    'cta_label' => 'Ver mi contrato', 'cta_url' => route('contracts.download', $steps['contrato']['contract']->id)]
                : ['title' => 'Tu contrato está en preparación', 'body' => $steps['contrato']['summary'], 'minutes' => null, 'cta_label' => null, 'cta_url' => null],

            'obligado' => self::nextForObligado($steps['obligado'] ?? []),

            'entrega' => ['title' => 'Casi listo: la entrega de tu inmueble', 'body' => $steps['entrega']['summary'], 'minutes' => null, 'cta_label' => null, 'cta_url' => null],

            default => ['title' => '¡Todo en orden!', 'body' => 'Completaste todos los pasos. Tu asesor te contactará ante cualquier novedad.', 'minutes' => null, 'cta_label' => null, 'cta_url' => null],
        };

        return $a + ['step' => $step, 'secondary' => $secondary];
    }

    private static function nextForObligado(array $step): array
    {
        $st = $step['os_status'] ?? null;
        if (! $st || ! $st['registered']) {
            return ['title' => 'Registra a tu obligado solidario', 'body' => 'Es un requisito de la póliza sin aval. Solo necesitamos su nombre y celular; los demás datos y sus documentos los capturas tú aquí mismo.',
                'minutes' => 2, 'cta_label' => 'Registrarlo', 'cta_url' => '#step-obligado'];
        }
        $name = $st['name'];
        if ($st['data_pct'] < 100) {
            return ['title' => "Llena los datos de {$name}", 'body' => 'Pídele su información (trabajo, referencias personales, antiguo arrendador…) y captúrala aquí; es el mismo cuestionario que el tuyo.',
                'minutes' => 10, 'cta_label' => 'Llenar sus datos', 'cta_url' => route('portal.expediente', ['para' => 'obligado'])];
        }
        if (! empty($st['docs_missing']) && ($st['docs']['falta'] + $st['docs']['corregir']) > 0) {
            return ['title' => "Sube los documentos de {$name}", 'body' => $step['summary'],
                'minutes' => 5, 'cta_label' => 'Subir sus documentos', 'cta_url' => route('portal.documents.index', ['para' => 'obligado'])];
        }

        return ['title' => "Tu asesor está revisando los documentos de {$name}", 'body' => $step['summary'], 'minutes' => null, 'cta_label' => null, 'cta_url' => null];
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

    /** "Tu obligado solidario": quién es, su avance y si ya está completo (datos + documentos aprobados). Lo captura el inquilino. */
    private static function obligado(RentalProcess $r, \App\Services\ObligadoSolidarioService $svc): array
    {
        $st = $svc->status($r);
        $base = ['key' => 'obligado', 'title' => 'Tu obligado solidario', 'os_status' => $st];

        if (! $st['registered']) {
            return $base + ['done' => false, 'action' => 'register_os',
                'summary' => 'Sin aval en CDMX, la póliza requiere un obligado solidario: una persona que responde junto contigo y aporta los mismos datos y documentos que tú. Regístrala (nombre y celular) y tú capturas sus datos y subes sus documentos desde aquí.'];
        }

        $docTotal = array_sum($st['docs']);
        $summary = $st['complete']
            ? "{$st['name']} completó su información y sus documentos fueron aprobados. ✅"
            : "{$st['name']} lleva {$st['data_pct']}% de sus datos y {$st['docs']['aprobado']} de {$docTotal} documentos aprobados."
                . ($st['started'] ? '' : ' Aún no se sube ningún documento suyo.');

        return $base + ['done' => $st['complete'], 'action' => $st['complete'] ? null : 'os_status', 'summary' => $summary];
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
                return $base + ['done' => false, 'action' => null, 'awaiting_owner' => true,
                    'summary' => 'Sin aval en CDMX, tu garantía es una póliza jurídica con Previsión Legal, quien realiza la investigación. Tu propietario elegirá el plan y cómo se reparte el costo; te avisamos en cuanto decida.'];
            }
            $approved = $poliza && $poliza->status === 'approved';
            $status = $poliza ? (PolizaJuridica::STATUSES[$poliza->status] ?? $poliza->status) : 'Pendiente';
            $decision = self::polizaDecision($r);
            $mine = $decision ? '$' . number_format($decision['split']['tenant']) . ' MXN' . ($decision['split']['tenant_pct'] < 100 ? ' (' . $decision['split']['tenant_pct'] . '%)' : '') : null;

            return $base + ['done' => $approved, 'plan' => $plan, 'poliza' => $poliza, 'decision' => $decision,
                'summary' => $approved
                    ? "Tu póliza {$plan->name} fue aprobada. ✅"
                    : "Tu propietario eligió el plan {$plan->name}" . ($mine ? ". Tu parte: {$mine}" : '') . ". Estado: {$status}. Tu asesor coordina el alta con Previsión Legal.",
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

    /**
     * La decisión del propietario con montos: total, reparto (inquilino/propietario), gastos de emisión y forma de pago.
     * Usa la "foto" guardada al decidir; si es una renta anterior (el inquilino eligió el plan), calcula con la tarifa vigente.
     *
     * @return array{plan:PolizaPlan, amount:float, split:array, emission_fee:float, payment_mode:string, decided_by:?string, tenant_paid:bool, owner_paid:bool}|null
     */
    public static function polizaDecision(RentalProcess $r): ?array
    {
        $plan = $r->polizaPlan;
        if (! $plan) {
            return null;
        }
        $amount = $r->poliza_quote_amount !== null ? (float) $r->poliza_quote_amount : (PolizaPricing::quote($plan, (float) $r->monthly_rent)['amount'] ?? null);
        if ($amount === null) {
            return null;
        }
        $fee = $r->poliza_emission_fee !== null ? (float) $r->poliza_emission_fee : (float) (PolizaPricing::sheet()?->emission_fee ?? 0);

        return [
            'plan' => $plan,
            'amount' => $amount,
            'split' => PolizaPricing::split($amount, (int) ($r->poliza_tenant_share ?? 100)), // rentas anteriores: la pagaba el inquilino
            'emission_fee' => $fee,
            'payment_mode' => $r->poliza_payment_mode ?: 'direct',
            'decided_by' => $r->poliza_decided_by,
            'tenant_paid' => (bool) $r->poliza_tenant_paid_at,
            'owner_paid' => (bool) $r->poliza_owner_paid_at,
        ];
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
