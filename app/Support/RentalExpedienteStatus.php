<?php

namespace App\Support;

use App\Models\Document;
use App\Models\RentalProcess;

/**
 * ¿El expediente del inquilino de una renta está completo y APROBADO?
 * Sirve para avisar al asesor que ya puede avanzar de etapa (no avanza solo:
 * pasar de etapa es una decisión del asesor). Reglas = lo obligatorio de
 * TenantDocumentChecklist; el acta de matrimonio / ID del cónyuge del aval son
 * condicionales y no se exigen aquí.
 */
class RentalExpedienteStatus
{
    /** Cada grupo se cumple con AL MENOS UNO de sus alternativas (o todas, si es 'all'). */
    const GROUPS = [
        'Identificación' => ['any_of' => [['ine_frente', 'ine_reverso'], ['pasaporte'], ['identificacion']]],
        'Comprobante de domicilio' => ['any_of' => [['comprobante_domicilio'], ['luz'], ['agua'], ['gas']]],
        'Comprobante de ingresos' => ['any_of' => [['nomina'], ['estado_cuenta'], ['cfdi_honorarios'], ['proof_of_income']]],
    ];

    /** Cuántos comprobantes de ingresos (uno por mes) se aprueban para dar el grupo por completo. */
    const INCOME_MONTHS = 3;

    const AVAL_REQUIRED = ['aval_ine_frente', 'aval_ine_reverso', 'aval_comprobante_domicilio', 'aval_escritura', 'aval_predial', 'aval_libertad_gravamen'];

    /**
     * @param  int|null  $clientId  a quién se evalúa: el INQUILINO (por defecto) o el obligado solidario (sin aval ni pagarés)
     * @return string[] lo que falta por aprobar (vacío = completo)
     */
    public static function missing(RentalProcess $rental, ?int $clientId = null): array
    {
        $forObligado = $clientId !== null;
        $tenantId = $clientId ?? $rental->tenant_client_id;
        $verified = Document::where('rental_process_id', $rental->id)
            ->where('status', 'verified')
            ->where(fn($q) => $forObligado ? $q->where('client_id', $tenantId) : $q->whereNull('client_id')->orWhere('client_id', $tenantId))
            ->pluck('category')->unique()->all();

        // Comprobantes de ingresos: los ÚLTIMOS 3 (uno por mes) aprobados de un mismo tipo, no basta con uno.
        $verifiedCounts = Document::where('rental_process_id', $rental->id)
            ->where('status', 'verified')
            ->where(fn($q) => $forObligado ? $q->where('client_id', $tenantId) : $q->whereNull('client_id')->orWhere('client_id', $tenantId))
            ->pluck('category')->countBy();

        $missing = [];
        foreach (self::GROUPS as $label => $rule) {
            $ok = false;
            if ($label === 'Comprobante de ingresos') {
                $ok = collect(['nomina', 'estado_cuenta', 'cfdi_honorarios'])->contains(fn($c) => $verifiedCounts->get($c, 0) >= self::INCOME_MONTHS)
                    || $verifiedCounts->get('proof_of_income', 0) >= 1;
                if (! $ok) {
                    $best = collect(['nomina', 'estado_cuenta', 'cfdi_honorarios'])->map(fn($c) => (int) $verifiedCounts->get($c, 0))->max();
                    $missing[] = 'Comprobante de ingresos (últimos ' . self::INCOME_MONTHS . ($best > 0 ? ", llevas {$best} aprobados" : '') . ')';
                }
                continue;
            }
            foreach ($rule['any_of'] as $combo) {
                if (! array_diff($combo, $verified)) {
                    $ok = true;
                    break;
                }
            }
            if (! $ok) {
                $missing[] = $label;
            }
        }

        if (! $forObligado && in_array($rental->guarantee_type, ['aval', 'aval_pagares'], true)) {
            foreach (array_diff(self::AVAL_REQUIRED, $verified) as $cat) {
                $missing[] = Document::CATEGORIES[$cat] ?? $cat;
            }
        }
        if (! $forObligado && in_array($rental->guarantee_type, ['pagares', 'aval_pagares'], true) && ! in_array('pagare', $verified, true)) {
            $missing[] = Document::CATEGORIES['pagare'];
        }

        return $missing;
    }

    public static function isComplete(RentalProcess $rental): bool
    {
        return $rental->tenant_client_id && ! self::missing($rental);
    }

    /**
     * Expediente COMPLETO del trato: el del inquilino Y, si el trato exige obligado solidario (póliza sin aval), el suyo
     * (datos completos + documentos aprobados). Es lo que dispara el aviso "expediente completo" al asesor.
     */
    public static function isFullyComplete(RentalProcess $rental): bool
    {
        if (! self::isComplete($rental)) {
            return false;
        }
        $os = app(\App\Services\ObligadoSolidarioService::class);

        return ! $os->isRequired($rental) || $os->status($rental)['complete'];
    }
}
