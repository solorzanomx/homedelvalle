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
    const STATE_LABELS = ['falta' => 'Falta', 'revision' => 'En revisión', 'aprobado' => 'Aprobado', 'corregir' => 'Corregir'];

    /**
     * @return array{groups:array<int,array>, counts:array<string,int>, next:?array}
     */
    public static function build(RentalProcess $rental, Client $client): array
    {
        $docs = Document::where('client_id', $client->id)->where('rental_process_id', $rental->id)->orderBy('created_at')->get();
        $labels = Document::CATEGORIES;
        $route = TenantRoadmap::route($rental);

        $row = function (string $key, string $label, array $cats, array $opts = []) use ($docs) {
            $mine = $docs->filter(fn($d) => in_array($d->category, $cats, true));
            $alive = $mine->reject(fn($d) => $d->status === 'rejected');
            $rejected = $mine->where('status', 'rejected');

            $state = match (true) {
                $alive->where('status', 'verified')->isNotEmpty() && $alive->where('status', 'received')->isEmpty() => 'aprobado',
                $alive->isNotEmpty() => 'revision',
                $rejected->isNotEmpty() => 'corregir',
                default => 'falta',
            };

            return $opts + [
                'key' => $key, 'label' => $label, 'cats' => $cats, 'slots' => 1, 'camera' => false, 'optional' => false, 'chips' => null, 'hint' => null,
                'state' => $state, 'state_label' => self::STATE_LABELS[$state],
                'reason' => $state === 'corregir' ? $rejected->sortByDesc('rejected_at')->first()?->rejection_reason : null,
                'uploaded' => $alive->count(),
            ];
        };

        $groups = [];

        if (! $rental->apartado_paid_at) {
            $groups[] = ['title' => 'Apartado', 'icon' => '🔑', 'rows' => [
                $row('comprobante_apartado', 'Comprobante de tu depósito de apartado', ['comprobante_apartado'], ['hint' => 'La captura de tu transferencia o el PDF del banco.']),
            ]];
        }

        $groups[] = ['title' => 'Identificación oficial', 'icon' => '🪪', 'rows' => [
            $row('ine_frente', 'INE — Frente', ['ine_frente'], ['camera' => true]),
            $row('ine_reverso', 'INE — Reverso', ['ine_reverso'], ['camera' => true]),
            $row('pasaporte', '¿No tienes INE? Usa tu pasaporte', ['pasaporte'], ['camera' => true, 'optional' => true]),
        ]];

        $groups[] = ['title' => 'Comprobante de domicilio', 'icon' => '🏠', 'rows' => [
            $row('domicilio', 'Recibo de luz, agua o gas', ['luz', 'agua', 'gas', 'comprobante_domicilio'], [
                'hint' => 'De los últimos 3 meses, a tu nombre. Mejor en PDF.',
                'chips' => [['cat' => 'luz', 'label' => 'Luz (CFE)'], ['cat' => 'agua', 'label' => 'Agua'], ['cat' => 'gas', 'label' => 'Gas']],
            ]),
        ]];

        $groups[] = ['title' => 'Comprobante de ingresos', 'icon' => '💼', 'rows' => [
            $row('ingresos', 'Nómina, estados de cuenta o CFDI', ['nomina', 'estado_cuenta', 'cfdi_honorarios', 'proof_of_income'], [
                'hint' => 'Con un solo tipo basta (los últimos 3 meses). En PDF se leen mejor.',
                'chips' => [
                    ['cat' => 'nomina', 'label' => 'Recibos de nómina', 'slots' => 3],
                    ['cat' => 'estado_cuenta', 'label' => 'Estados de cuenta', 'slots' => 3],
                    ['cat' => 'cfdi_honorarios', 'label' => 'CFDI de honorarios', 'slots' => 1],
                ],
            ]),
        ]];

        if ($route === TenantRoadmap::ROUTE_AVAL) {
            $optional = ['aval_acta_matrimonio', 'aval_id_conyuge'];
            $groups[] = ['title' => 'Documentos de tu aval', 'icon' => '🛡️', 'rows' => collect(TenantDocumentChecklist::AVAL)
                ->map(fn($label, $cat) => $row($cat, $label, [$cat], ['camera' => in_array($cat, ['aval_ine_frente', 'aval_ine_reverso'], true), 'optional' => in_array($cat, $optional, true)]))
                ->values()->all()];
        }

        // Conteo y "siguiente": solo lo obligatorio. Identificación se cumple con INE (frente+reverso) o con pasaporte;
        // el resto de alternativas ya vienen agrupadas en una sola fila.
        $required = collect($groups)->flatMap(fn($g) => $g['rows'])->reject(fn($r) => $r['optional'])->values();
        $pasaporteOk = collect($groups)->flatMap(fn($g) => $g['rows'])->firstWhere('key', 'pasaporte')['state'] ?? 'falta';
        if (in_array($pasaporteOk, ['aprobado', 'revision'], true)) {
            $required = $required->reject(fn($r) => in_array($r['key'], ['ine_frente', 'ine_reverso'], true))->values();
        }

        $counts = array_fill_keys(array_keys(self::STATE_LABELS), 0);
        foreach ($required as $r) {
            $counts[$r['state']]++;
        }
        $next = $required->first(fn($r) => in_array($r['state'], ['corregir', 'falta'], true));

        return ['groups' => $groups, 'counts' => $counts, 'next' => $next];
    }
}
