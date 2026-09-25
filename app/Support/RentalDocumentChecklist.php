<?php

namespace App\Support;

use App\Models\Document;
use App\Models\RentalProcess;

/**
 * Qué documentos aplican a UNA renta (2026-09-25). Antes la pestaña de
 * Documentos del CRM pintaba Document::CATEGORIES completo (~75 categorías,
 * incluyendo las de venta, compradores, avales de otros tratos, etc.).
 *
 * Aquí se arma la lista por persona/tema y se ajusta a la garantía del
 * trato (aval, pagarés…). Nada se pierde: lo que ya esté subido en una
 * categoría que no aparece se muestra en la sección "Otros documentos".
 */
class RentalDocumentChecklist
{
    const INQUILINO = [
        'ine_frente', 'ine_reverso', 'pasaporte', 'identificacion', 'tenant_id',
        'comprobante_domicilio', 'luz', 'agua', 'gas',
        'nomina', 'estado_cuenta', 'cfdi_honorarios', 'proof_of_income',
        'references', 'credit_report',
    ];

    const PROPIETARIO = [
        'ine_frente', 'ine_reverso', 'pasaporte', 'identificacion', 'owner_id',
        'comprobante_domicilio', 'escritura', 'predial', 'libertad_gravamen',
        'acta_matrimonio', 'constancia_situacion_fiscal', 'reglamento_condominio',
    ];

    const PAGOS = ['comprobante_apartado', 'recibo_apartado', 'comprobante_pago_investigacion', 'recibo_investigacion'];

    const CONTRATOS = ['contrato_exclusiva_renta', 'commission_contract', 'rental_contract', 'poliza_contract', 'other'];

    /**
     * @return array<int, array{key:string,title:string,icon:string,categories:array<string,string>,client:string}>
     *   client: 'tenant' | 'owner' | 'any' — a quién pertenecen los documentos de la sección.
     */
    public static function sections(RentalProcess $rental): array
    {
        $labels = Document::CATEGORIES;
        $pick = fn(array $keys) => array_intersect_key(array_replace(array_flip($keys), $labels), array_flip($keys));

        $sections = [
            ['key' => 'inquilino', 'title' => 'Inquilino' . ($rental->tenantClient ? ' — ' . $rental->tenantClient->name : ''),
                'icon' => '🧑', 'client' => 'tenant', 'categories' => $pick(self::INQUILINO)],
        ];

        $garantia = match ($rental->guarantee_type) {
            'aval' => TenantDocumentChecklist::AVAL,
            'pagares' => TenantDocumentChecklist::PAGARE,
            'aval_pagares' => TenantDocumentChecklist::AVAL + TenantDocumentChecklist::PAGARE,
            default => [],
        };
        if ($garantia) {
            $sections[] = ['key' => 'garantia', 'title' => 'Garantía — ' . $rental->guarantee_type_label,
                'icon' => '🛡️', 'client' => 'any', 'categories' => $pick(array_keys($garantia))];
        }

        $sections[] = ['key' => 'propietario', 'title' => 'Propietario' . ($rental->ownerClient ? ' — ' . $rental->ownerClient->name : ''),
            'icon' => '🏠', 'client' => 'owner', 'categories' => $pick(self::PROPIETARIO)];
        $sections[] = ['key' => 'pagos', 'title' => 'Apartado y cuota de investigación',
            'icon' => '💳', 'client' => 'any', 'categories' => $pick(self::PAGOS)];
        $sections[] = ['key' => 'contratos', 'title' => 'Contratos',
            'icon' => '📑', 'client' => 'any', 'categories' => $pick(self::CONTRATOS)];

        return $sections;
    }

    /** ¿El documento pertenece a esta sección (por persona)? */
    public static function belongs(Document $doc, array $section, RentalProcess $rental): bool
    {
        return match ($section['client']) {
            'tenant' => ! $doc->client_id || ! $rental->tenant_client_id || $doc->client_id === $rental->tenant_client_id,
            'owner' => ! $doc->client_id || $doc->client_id === $rental->owner_client_id,
            default => true,
        };
    }

    /**
     * Arma lo que la vista pinta: por sección, las categorías con documentos,
     * las que faltan y los "otros" (subidos en categorías fuera de la lista).
     */
    public static function build(RentalProcess $rental): array
    {
        $docs = $rental->documents->sortByDesc('created_at')->values();
        $shown = [];
        $out = [];

        foreach (self::sections($rental) as $section) {
            $present = [];
            $missing = [];
            foreach ($section['categories'] as $key => $label) {
                $catDocs = $docs->filter(fn($d) => $d->category === $key && self::belongs($d, $section, $rental))
                    ->reject(fn($d) => isset($shown[$d->id]))->values();
                foreach ($catDocs as $d) { $shown[$d->id] = true; }
                $catDocs->isEmpty() ? $missing[$key] = $label : $present[] = ['key' => $key, 'label' => $label, 'docs' => $catDocs];
            }
            $out[] = $section + ['present' => $present, 'missing' => $missing];
        }

        $others = $docs->reject(fn($d) => isset($shown[$d->id]))->values();

        return ['sections' => $out, 'others' => $others];
    }
}
