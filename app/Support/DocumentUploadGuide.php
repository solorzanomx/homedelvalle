<?php

namespace App\Support;

/**
 * Guía de cómo subir cada tipo de documento al Portal (2026-09-25).
 *
 * Un cliente fotografió la pantalla de su teléfono con el estado de cuenta
 * adentro — ilegible para verificar. Aquí vive UNA sola definición de qué
 * formato conviene y qué sí/no hacer por tipo de documento; la leyenda del
 * Portal (partial portal._upload_tips) y las revisiones de calidad
 * (DocumentQualityService) la comparten, así los textos no se desalinean.
 */
class DocumentUploadGuide
{
    const KIND_ID = 'id';
    const KIND_STATEMENT = 'statement';
    const KIND_UTILITY = 'utility';
    const KIND_LEGAL = 'legal';
    const KIND_PAYMENT = 'payment';
    const KIND_GENERIC = 'generic';

    const KINDS = [
        'ine_frente' => self::KIND_ID, 'ine_reverso' => self::KIND_ID, 'pasaporte' => self::KIND_ID,
        'identificacion' => self::KIND_ID, 'tenant_id' => self::KIND_ID, 'owner_id' => self::KIND_ID,
        'aval_ine_frente' => self::KIND_ID, 'aval_ine_reverso' => self::KIND_ID, 'aval_id_conyuge' => self::KIND_ID,

        'estado_cuenta' => self::KIND_STATEMENT, 'nomina' => self::KIND_STATEMENT, 'cfdi_honorarios' => self::KIND_STATEMENT,
        'proof_of_income' => self::KIND_STATEMENT, 'carta_preautorizacion' => self::KIND_STATEMENT,
        'constancia_situacion_fiscal' => self::KIND_STATEMENT, 'carta_finiquito' => self::KIND_STATEMENT,

        'comprobante_domicilio' => self::KIND_UTILITY, 'aval_comprobante_domicilio' => self::KIND_UTILITY,
        'luz' => self::KIND_UTILITY, 'agua' => self::KIND_UTILITY, 'gas' => self::KIND_UTILITY,

        'comprobante_apartado' => self::KIND_PAYMENT, 'comprobante_pago_investigacion' => self::KIND_PAYMENT,

        'escritura' => self::KIND_LEGAL, 'predial' => self::KIND_LEGAL, 'libertad_gravamen' => self::KIND_LEGAL,
        'aval_escritura' => self::KIND_LEGAL, 'aval_predial' => self::KIND_LEGAL, 'aval_libertad_gravamen' => self::KIND_LEGAL,
        'acta_matrimonio' => self::KIND_LEGAL, 'aval_acta_matrimonio' => self::KIND_LEGAL, 'acta_nacimiento' => self::KIND_LEGAL,
        'acta_divorcio' => self::KIND_LEGAL, 'convenio_divorcio' => self::KIND_LEGAL, 'testamento' => self::KIND_LEGAL,
        'declaratoria_herederos' => self::KIND_LEGAL, 'reglamento_condominio' => self::KIND_LEGAL, 'planos' => self::KIND_LEGAL,
        'adjudicacion_conyugal' => self::KIND_LEGAL, 'cancelacion_hipoteca' => self::KIND_LEGAL,
        'carta_no_adeudo_mantenimiento' => self::KIND_LEGAL, 'certificado_no_adeudo_contribuciones' => self::KIND_LEGAL,
        'avaluo_notarial' => self::KIND_LEGAL, 'pagare' => self::KIND_LEGAL, 'documento_migratorio' => self::KIND_LEGAL,
        'curp' => self::KIND_LEGAL,
    ];

    public static function kind(?string $category): string
    {
        return self::KINDS[$category] ?? self::KIND_GENERIC;
    }

    /**
     * @return array{kind:string, headline:string, do:string[], dont:string[], pdf_ok:bool}
     */
    public static function for(?string $category): array
    {
        $kind = self::kind($category);

        return ['kind' => $kind] + match ($kind) {
            self::KIND_ID => [
                'headline' => 'Foto clara de tu identificación',
                'pdf_ok' => true,
                'do' => [
                    'Encuadra solo la credencial, llenando la foto (en el Portal, el botón "Usar cámara" te ayuda).',
                    'Sobre una mesa lisa, con buena luz y sin reflejos.',
                    'Que se lean todos los datos y se vea bien tu foto.',
                ],
                'dont' => [
                    'No fotografíes una pantalla ni una fotocopia borrosa.',
                    'No dejes dedos sobre la credencial ni mucho espacio alrededor.',
                ],
            ],
            self::KIND_STATEMENT => [
                'headline' => 'Súbelo en PDF, descargado directo de tu banco o de tu empresa',
                'pdf_ok' => true,
                'do' => [
                    'Descárgalo en PDF desde tu banca en línea o app: es lo más legible y lo aceptamos completo.',
                    'Incluye todas las páginas del periodo solicitado.',
                    'Si solo tienes foto: documento plano sobre una mesa, con buena luz, completo y sin cortar.',
                ],
                'dont' => [
                    'No le tomes foto a la pantalla del celular o de la computadora.',
                    'No mandes capturas recortadas ni con solo una parte del documento.',
                ],
            ],
            self::KIND_UTILITY => [
                'headline' => 'Recibo reciente (máximo 3 meses), en PDF o en foto plana',
                'pdf_ok' => true,
                'do' => [
                    'Si tu recibo es digital, descarga el PDF del sitio del proveedor.',
                    'Que se vean tu nombre, la dirección completa y la fecha de emisión.',
                    'Si es foto: recibo extendido sobre una mesa, con buena luz, sin sombras.',
                ],
                'dont' => [
                    'No le tomes foto a la pantalla del celular o de la computadora.',
                    'No lo subas doblado, cortado o con la mitad de la hoja.',
                ],
            ],
            self::KIND_LEGAL => [
                'headline' => 'Documento completo, en PDF o foto hoja por hoja',
                'pdf_ok' => true,
                'do' => [
                    'Si lo tienes escaneado, súbelo en PDF con todas las páginas.',
                    'Si es foto: una página por foto, plana, con buena luz y los cuatro bordes visibles.',
                    'Que los sellos y las firmas se lean.',
                ],
                'dont' => [
                    'No le tomes foto a una pantalla.',
                    'No subas páginas cortadas, movidas o con sombra.',
                ],
            ],
            self::KIND_PAYMENT => [
                'headline' => 'Comprobante de tu transferencia o depósito',
                'pdf_ok' => true,
                'do' => [
                    'Captura de pantalla de tu app o el PDF del comprobante que descargas del banco.',
                    'Que se vean el monto, la fecha, el beneficiario y la clave de rastreo.',
                ],
                'dont' => [
                    'No subas una captura donde falten el monto o la fecha.',
                ],
            ],
            default => [
                'headline' => 'Súbelo lo más legible posible',
                'pdf_ok' => true,
                'do' => [
                    'Prefiere el PDF original. Si es foto: plana, con buena luz y completa.',
                ],
                'dont' => [
                    'No le tomes foto a una pantalla ni subas imágenes borrosas o cortadas.',
                ],
            ],
        };
    }

    /** Frase corta para el mensaje de rechazo, según el tipo de documento. */
    public static function betterWay(?string $category): string
    {
        return match (self::kind($category)) {
            self::KIND_STATEMENT => 'Descárgalo en PDF desde tu banca en línea o de tu empresa y súbelo de nuevo.',
            self::KIND_UTILITY => 'Descarga el recibo en PDF del sitio de tu proveedor, o tómale foto sobre una mesa con buena luz.',
            self::KIND_ID => 'Encuadra solo tu identificación, llenando la foto, con buena luz y sin reflejos.',
            self::KIND_LEGAL => 'Súbelo en PDF, o tómale foto plana sobre una mesa con buena luz.',
            default => 'Súbelo en PDF, o tómale foto plana sobre una mesa con buena luz.',
        };
    }
}
