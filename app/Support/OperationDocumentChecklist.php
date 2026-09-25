<?php

namespace App\Support;

use App\Models\Operation;

/**
 * Qué documentos aplican a una Operation según su tipo (2026-09-25). Antes la
 * pestaña "Docs" pintaba las ~75 categorías completas (vendedor, comprador,
 * avales de renta…) en cualquier operación.
 *
 *  venta / captacion → vendedor: personal (+ estado civil), inmueble, notaría
 *  comprador         → identificación, ingresos/crédito, oferta y compra
 *  renta             → propietario que renta
 *  inquilino         → inquilino (misma lista que la renta)
 * Tipo desconocido → sin secciones: todo cae en "Otros" y el selector muestra todo.
 */
class OperationDocumentChecklist
{
    const CONTRATOS_VENTA = ['contrato_exclusiva', 'adendum_comision', 'commission_contract', 'oferta_compra', 'contrato_compraventa', 'opinion_valor', 'presentation_pdf', 'propuesta_servicios', 'other'];

    const COMPRADOR = [
        'identificacion', 'ine_frente', 'ine_reverso', 'pasaporte', 'curp', 'comprobante_domicilio',
        'constancia_situacion_fiscal', 'estado_cuenta', 'nomina', 'proof_of_income', 'cfdi_honorarios',
        'carta_preautorizacion', 'acta_matrimonio',
    ];

    public static function sections(Operation $op): array
    {
        $s = fn(...$a) => DocumentChecklist::section(...$a);
        $client = $op->client;

        return match ($op->type) {
            'venta', 'captacion' => [
                $s('vendedor', 'Vendedor' . ($client ? ' — ' . $client->name : ''), '🧑', array_merge(
                    ['ine_frente', 'ine_reverso'], // el Portal las pide por separado (además de 'identificacion')
                    array_keys(SellerDocumentChecklist::PERSONAL),
                    array_keys(SellerDocumentChecklist::estadoCivilDocs($client?->marital_status)),
                )),
                $s('inmueble', 'Inmueble', '🏠', array_keys(SellerDocumentChecklist::INMUEBLE)),
                $s('notaria', 'Trámites de notaría', '⚖️', array_keys(SellerDocumentChecklist::NOTARIAL)),
                $s('pagos', 'Apartado', '💳', ['comprobante_apartado', 'recibo_apartado']),
                $s('contratos', 'Contratos, ofertas y documentos de la operación', '📑', self::CONTRATOS_VENTA),
            ],
            'comprador' => [
                $s('comprador', 'Comprador' . ($client ? ' — ' . $client->name : ''), '🧑', self::COMPRADOR),
                $s('pagos', 'Apartado', '💳', ['comprobante_apartado', 'recibo_apartado']),
                $s('contratos', 'Oferta y compra', '📑', ['oferta_compra', 'contrato_compraventa', 'other']),
            ],
            'inquilino' => [
                $s('inquilino', 'Inquilino' . ($client ? ' — ' . $client->name : ''), '🧑', RentalDocumentChecklist::INQUILINO),
                $s('pagos', 'Apartado y cuota de investigación', '💳', RentalDocumentChecklist::PAGOS),
                $s('contratos', 'Contratos', '📑', RentalDocumentChecklist::CONTRATOS),
            ],
            'renta' => [
                $s('propietario', 'Propietario' . ($client ? ' — ' . $client->name : ''), '🏠', RentalDocumentChecklist::PROPIETARIO),
                $s('contratos', 'Contratos', '📑', array_merge(RentalDocumentChecklist::CONTRATOS, ['contrato_exclusiva_renta'])),
            ],
            default => [],
        };
    }

    public static function build(Operation $op): array
    {
        return DocumentChecklist::assemble($op->documents, self::sections($op), fn() => true);
    }

    /** Categorías para el selector de "Subir documento": las del caso; todas si el tipo no se conoce. */
    public static function uploadGroups(array $sections): array
    {
        return $sections ?: [];
    }
}
