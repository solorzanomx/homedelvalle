<?php

namespace App\Support;

use App\Models\Client;
use App\Models\Document;
use App\Models\RentalProcess;

/**
 * La lista "Mis documentos" del INQUILINO (2026-09-26): cada documento que le toca subir, con su estado
 * en lenguaje simple (Falta / En revisión / Aprobado / Corregir) para que sepa exactamente qué hacer.
 * Solo LEE; subir/borrar lo hace el componente Livewire portal.document-uploader.
 *
 * Reglas de cumplimiento = las de RentalExpedienteStatus (identificación: INE frente+reverso o pasaporte;
 * domicilio: luz/agua/gas/comprobante; ingresos: nómina, estado de cuenta o CFDI). El aval solo aparece si la
 * ruta de garantía es aval.
 */
class TenantDocumentRows
{
    const STATE_LABELS = ['falta' => 'Falta', 'parcial' => 'Faltan', 'revision' => 'En revisión', 'aprobado' => 'Aprobado', 'corregir' => 'Corregir'];

    /** Comprobantes de ingresos: se piden los ÚLTIMOS 3 (uno por mes) — nómina, estados de cuenta o CFDI. */
    const INCOME_MONTHS = 3;

    /**
     * Identificación: una u otra. 'ine' (frente + vuelta) si ya subió alguna cara de la INE; 'pasaporte' si subió el
     * pasaporte; si no ha subido nada, null (primero elige cuál usará). `$open` = la fila que acaba de tocar.
     */
    public static function idMode(\Illuminate\Support\Collection $docs, ?string $open = null): ?string
    {
        $cats = $docs->pluck('category');
        if ($cats->contains('ine_frente') || $cats->contains('ine_reverso')) {
            return 'ine';
        }
        if ($cats->contains('pasaporte')) {
            return 'pasaporte';
        }

        return match ($open) {
            'ine_frente', 'ine_reverso' => 'ine',
            'pasaporte' => 'pasaporte',
            default => null,
        };
    }

    /**
     * Estado de un documento (o de un grupo de archivos) que requiere $needed archivos.
     *
     * @return array{0:string, 1:int, 2:?string} [estado, archivos vigentes, motivo del rechazo]
     */
    public static function stateFor(\Illuminate\Support\Collection $mine, int $needed = 1): array
    {
        $alive = $mine->reject(fn($d) => $d->status === 'rejected');
        $rejected = $mine->where('status', 'rejected');
        $uploaded = $alive->count();

        if ($uploaded >= $needed) {
            $state = $alive->where('status', 'received')->isNotEmpty() ? 'revision' : 'aprobado';
        } elseif ($rejected->isNotEmpty()) {
            $state = 'corregir';
        } elseif ($uploaded > 0) {
            $state = 'parcial';
        } else {
            $state = 'falta';
        }

        return [$state, $uploaded, $state === 'corregir' ? $rejected->sortByDesc('rejected_at')->first()?->rejection_reason : null];
    }

    /**
     * @return array{groups:array<int,array>, counts:array<string,int>, next:?array}
     */
    public static function build(RentalProcess $rental, Client $client, ?string $open = null): array
    {
        $docs = Document::where('client_id', $client->id)->where('rental_process_id', $rental->id)->orderBy('created_at')->get();
        $labels = Document::CATEGORIES;
        $route = TenantRoadmap::route($rental);

        $row = function (string $key, string $label, array $cats, array $opts = []) use ($docs) {
            $mine = $docs->filter(fn($d) => in_array($d->category, $cats, true));
            $needed = $opts['needed'] ?? 1;
            [$state, $uploaded, $reason] = self::stateFor($mine, $needed);

            return $opts + [
                'key' => $key, 'label' => $label, 'cats' => $cats, 'slots' => 1, 'camera' => false, 'optional' => false, 'chips' => null, 'hint' => null, 'needed' => $needed,
                'state' => $state,
                'state_label' => $state === 'parcial' ? 'Faltan ' . ($needed - $uploaded) . ' de ' . $needed : self::STATE_LABELS[$state],
                'reason' => $reason, 'uploaded' => $uploaded,
            ];
        };

        $groups = [];

        if (! $rental->apartado_paid_at) {
            $groups[] = ['title' => 'Apartado', 'icon' => '🔑', 'rows' => [
                $row('comprobante_apartado', 'Comprobante de tu depósito de apartado', ['comprobante_apartado'], ['hint' => 'La captura de tu transferencia o el PDF del banco.']),
            ]];
        }

        $idMode = self::idMode($docs, $open);
        $idRows = match ($idMode) {
            'ine' => [
                $row('ine_frente', 'INE — Frente', ['ine_frente'], ['camera' => true]),
                $row('ine_reverso', 'INE — Vuelta', ['ine_reverso'], ['camera' => true]),
            ],
            'pasaporte' => [$row('pasaporte', 'Pasaporte (hoja de datos)', ['pasaporte'], ['camera' => true])],
            // Aún no elige: una sola fila para escoger (INE o pasaporte). Con INE se piden frente y vuelta.
            default => [$row('identificacion', 'Identificación oficial', ['ine_frente', 'ine_reverso', 'pasaporte'], [
                'hint' => 'Elige una: con INE te pedimos frente y vuelta; con pasaporte, la hoja de datos.',
                'chips' => [['label' => 'INE (frente y vuelta)', 'open' => 'ine_frente'], ['label' => 'Pasaporte', 'open' => 'pasaporte']],
            ])],
        };
        $groups[] = ['title' => 'Identificación oficial', 'icon' => '🪪', 'rows' => $idRows];

        $groups[] = ['title' => 'Comprobante de domicilio', 'icon' => '🏠', 'rows' => [
            $row('domicilio', 'Recibo de luz, agua o gas', ['luz', 'agua', 'gas', 'comprobante_domicilio'], [
                'hint' => 'De los últimos 3 meses, a tu nombre. Mejor en PDF.',
                'chips' => [['cat' => 'luz', 'label' => 'Luz (CFE)'], ['cat' => 'agua', 'label' => 'Agua'], ['cat' => 'gas', 'label' => 'Gas']],
            ]),
        ]];

        // Ingresos: los ÚLTIMOS 3 (uno por mes) de UN mismo tipo. El tipo "elegido" es el que ya tiene archivos; el
        // avance se mide contra 3 ("Faltan 2 de 3").
        $incomeCats = ['nomina', 'estado_cuenta', 'cfdi_honorarios'];
        $chosenIncome = collect($incomeCats)
            ->filter(fn($c) => $docs->where('category', $c)->isNotEmpty())
            ->sortByDesc(fn($c) => $docs->where('category', $c)->count())
            ->first(); // null = aún no sube ninguno
        $groups[] = ['title' => 'Comprobante de ingresos', 'icon' => '💼', 'rows' => [
            $row('ingresos', 'Los últimos 3 meses', $chosenIncome ? [$chosenIncome] : array_merge($incomeCats, ['proof_of_income']), [
                'needed' => self::INCOME_MONTHS,
                'chosen_cat' => $chosenIncome,
                'hint' => 'Elige un tipo y sube uno por cada uno de los últimos 3 meses (nómina, estado de cuenta o CFDI). En PDF se leen mejor.',
                'chips' => [
                    ['cat' => 'nomina', 'label' => 'Recibos de nómina', 'slots' => self::INCOME_MONTHS],
                    ['cat' => 'estado_cuenta', 'label' => 'Estados de cuenta', 'slots' => self::INCOME_MONTHS],
                    ['cat' => 'cfdi_honorarios', 'label' => 'CFDI de honorarios', 'slots' => self::INCOME_MONTHS],
                ],
            ]),
        ]];

        if ($route === TenantRoadmap::ROUTE_AVAL) {
            $optional = ['aval_acta_matrimonio', 'aval_id_conyuge'];
            $groups[] = ['title' => 'Documentos de tu aval', 'icon' => '🛡️', 'rows' => collect(TenantDocumentChecklist::AVAL)
                ->map(fn($label, $cat) => $row($cat, $label, [$cat], ['camera' => in_array($cat, ['aval_ine_frente', 'aval_ine_reverso'], true), 'optional' => in_array($cat, $optional, true)]))
                ->values()->all()];
        }

        // Conteo y "siguiente": solo lo obligatorio. 'parcial' (faltan N de 3) cuenta como falta.
        $required = collect($groups)->flatMap(fn($g) => $g['rows'])->reject(fn($r) => $r['optional'])->values();
        $counts = ['falta' => 0, 'revision' => 0, 'aprobado' => 0, 'corregir' => 0];
        foreach ($required as $r) {
            $counts[$r['state'] === 'parcial' ? 'falta' : $r['state']]++;
        }
        $next = $required->first(fn($r) => in_array($r['state'], ['corregir', 'falta', 'parcial'], true));

        return ['groups' => $groups, 'counts' => $counts, 'next' => $next];
    }
}
