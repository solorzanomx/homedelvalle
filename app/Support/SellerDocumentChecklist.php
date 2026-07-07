<?php

namespace App\Support;

use App\Models\Client;

/**
 * Checklist real de documentación de vendedor (notaría/broker, 2026-07-07):
 * qué se le pide al CLIENTE durante el proceso (Personal + Estado Civil +
 * Inmueble) vs. lo que tramita la NOTARÍA (no se le pide subir al cliente,
 * solo se trackea si ya llegó). Mismo espíritu que
 * Captacion::REQUIRED_DOCS_ETAPA1/getApplicableOptionalDocs() pero para
 * todo el proceso de venta, no solo la primera etapa de captación.
 *
 * documento_migratorio y adjudicacion_conyugal quedan en Document::CATEGORIES
 * (se pueden subir/pedir manualmente) pero no se auto-requieren aquí — no
 * hay un campo confiable hoy que dispare "es extranjero" o "el inmueble se
 * adquirió en sociedad conyugal" sin riesgo de falsos positivos.
 */
class SellerDocumentChecklist
{
    const PERSONAL = [
        'identificacion' => 'Identificación Oficial',
        'pasaporte' => 'Pasaporte Vigente',
        'curp' => 'CURP',
        'constancia_situacion_fiscal' => 'Constancia de Situación Fiscal',
        'comprobante_domicilio' => 'Comprobante de Domicilio (no mayor a 3 meses)',
        'estado_cuenta' => 'Estado de Cuenta o Carátula Bancaria (CLABE)',
    ];

    const INMUEBLE = [
        'escritura' => 'Primer Testimonio de Escritura Pública',
        'cancelacion_hipoteca' => 'Cancelación de Hipoteca / Liberación de Gravamen',
        'carta_finiquito' => 'Carta Finiquito Bancaria',
        'predial' => 'Boleta y Comprobante de Pago Predial',
        'agua' => 'Boleta y Comprobante de Pago de Agua',
        'carta_no_adeudo_mantenimiento' => 'Carta de No Adeudo de Mantenimiento',
        'reglamento_condominio' => 'Reglamento de Condominio',
    ];

    // Lo tramita la notaría, no el cliente — se trackea, no se pide subir en el Portal.
    const NOTARIAL = [
        'libertad_gravamen' => 'Certificado de Libertad de Gravámenes',
        'certificado_no_adeudo_contribuciones' => 'Certificado de No Adeudo de Contribuciones',
        'avaluo_notarial' => 'Avalúo Notarial/Fiscal',
        'calculo_impuestos_notariales' => 'Cálculo de Impuestos y Gastos Notariales',
    ];

    public static function estadoCivilDocs(?string $maritalStatus): array
    {
        return match ($maritalStatus) {
            'casado', 'union_libre' => ['acta_matrimonio' => 'Acta de Matrimonio'],
            'divorciado' => [
                'acta_divorcio' => 'Acta de Divorcio',
                'convenio_divorcio' => 'Sentencia/Convenio de Divorcio',
            ],
            default => [],
        };
    }

    /** Documentos que se le piden subir al cliente vendedor (sin notarial). */
    public static function clientFacing(Client $client): array
    {
        return self::PERSONAL + self::estadoCivilDocs($client->marital_status) + self::INMUEBLE;
    }
}
