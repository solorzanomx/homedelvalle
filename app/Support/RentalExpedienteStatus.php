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

    const AVAL_REQUIRED = ['aval_ine_frente', 'aval_ine_reverso', 'aval_comprobante_domicilio', 'aval_escritura', 'aval_predial', 'aval_libertad_gravamen'];

    /** @return string[] lo que falta por aprobar (vacío = completo) */
    public static function missing(RentalProcess $rental): array
    {
        $tenantId = $rental->tenant_client_id;
        $verified = Document::where('rental_process_id', $rental->id)
            ->where('status', 'verified')
            ->where(fn($q) => $q->whereNull('client_id')->orWhere('client_id', $tenantId))
            ->pluck('category')->unique()->all();

        $missing = [];
        foreach (self::GROUPS as $label => $rule) {
            $ok = false;
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

        if (in_array($rental->guarantee_type, ['aval', 'aval_pagares'], true)) {
            foreach (array_diff(self::AVAL_REQUIRED, $verified) as $cat) {
                $missing[] = Document::CATEGORIES[$cat] ?? $cat;
            }
        }
        if (in_array($rental->guarantee_type, ['pagares', 'aval_pagares'], true) && ! in_array('pagare', $verified, true)) {
            $missing[] = Document::CATEGORIES['pagare'];
        }

        return $missing;
    }

    public static function isComplete(RentalProcess $rental): bool
    {
        return $rental->tenant_client_id && ! self::missing($rental);
    }
}
