<?php

namespace App\Support;

/**
 * Checklist real de documentación de arrendatario (2026-09-21, basado en el
 * cuestionario en papel "Persona Física" que se usaba antes de tener
 * Portal) — qué se le pide al inquilino durante la calificación. Mismo
 * espíritu que SellerDocumentChecklist pero para renta.
 *
 * PENDIENTE DE VERIFICAR (no lo resuelve esta clase): como sí se consulta
 * Buró de Crédito, la Ley para Regular las Sociedades de Información
 * Crediticia (LRSIC) exige una autorización POR ESCRITO específica para esa
 * consulta, separada del Aviso de Privacidad / Acuerdo de Confidencialidad
 * generales. Confirmar con el Acuerdo de Confidencialidad publicado en
 * /admin/legal si ya la incluye — si no, agregar esa cláusula antes de
 * apoyarse solo en `credit_report` como si fuera un checkbox suficiente.
 */
class TenantDocumentChecklist
{
    const IDENTIFICACION = [
        'ine_frente' => 'INE — Frente',
        'ine_reverso' => 'INE — Reverso',
        'comprobante_domicilio' => 'Comprobante de Domicilio (no mayor a 3 meses)',
    ];

    const INGRESOS = [
        'nomina' => 'Recibos de Nómina (últimos 3)',
        'estado_cuenta' => 'Estado de Cuenta (últimos 3 meses)',
        'cfdi_honorarios' => 'CFDI de Honorarios',
        'proof_of_income' => 'Otro comprobante de ingresos',
    ];

    const REFERENCIAS = [
        'references' => 'Referencias Personales (nombre, teléfono y relación de al menos 2 personas)',
    ];

    const CREDITO = [
        'credit_report' => 'Autorización de consulta / Reporte de Buró de Crédito',
    ];

    // Garantía — depende de qué tipo defina el asesor (RentalProcess::guarantee_type).
    const AVAL = [
        'aval_ine_frente' => 'INE Aval — Frente',
        'aval_ine_reverso' => 'INE Aval — Reverso',
        'aval_escritura' => 'Escritura del Inmueble en Garantía',
        'aval_predial' => 'Predial del Inmueble en Garantía',
        'aval_libertad_gravamen' => 'Libertad de Gravamen del Inmueble en Garantía',
    ];

    const PAGARE = [
        'pagare' => 'Pagaré',
    ];

    /** Documentos que se le piden subir al inquilino, sin importar el tipo de garantía. */
    public static function clientFacing(): array
    {
        return self::IDENTIFICACION + self::INGRESOS + self::REFERENCIAS + self::CREDITO;
    }
}
