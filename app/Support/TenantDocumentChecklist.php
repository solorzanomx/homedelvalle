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
    // 2026-09-24: se consolida a una sola línea de "identificación oficial"
    // (antes venía separada en INE Frente / INE Reverso, lo cual confundía
    // en la página pública — internamente el Portal sigue pidiendo ambas
    // fotos, o la carátula del pasaporte, según el tipo que elija el
    // inquilino; ver App\Livewire\Portal\DocumentUploader).
    const IDENTIFICACION = [
        'identificacion_oficial' => 'Identificación oficial vigente (INE, pasaporte, etc.)',
        'comprobante_domicilio' => 'Comprobante de domicilio no mayor a 3 meses (agua, luz o gas)',
    ];

    // Datos laborales: se capturan como campos (no como documento) al inicio
    // de la sección de Ingresos del Portal — se listan aquí para que el
    // checklist público refleje lo que realmente se pide primero.
    const DATOS_LABORALES = [
        'datos_laborales' => 'Datos laborales (empresa, puesto, antigüedad e ingreso mensual)',
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

    // 2026-09-24: ya no se sube como documento — se recaba la autorización
    // dentro del propio proceso del Portal. Se deja el requisito listado
    // porque la consulta sí se realiza.
    const CREDITO = [
        'credit_report' => 'Autorización para consultar tu Buró de Crédito',
    ];

    // Garantía — depende de qué tipo defina el asesor (RentalProcess::guarantee_type).
    // Lista oficial (formulario "Lista de documentos" del cuestionario en papel,
    // 2026-09-22): identificación, comprobante de domicilio, boleta predial
    // fehaciente, escritura pública con sello de RPP, y — condicionales —
    // acta de matrimonio (si aparece casado en la escritura) e identificación
    // del cónyuge (si el inmueble es de bienes mancomunados).
    const AVAL = [
        'aval_ine_frente' => 'INE Aval — Frente',
        'aval_ine_reverso' => 'INE Aval — Reverso',
        'aval_comprobante_domicilio' => 'Comprobante de Domicilio (Aval)',
        'aval_escritura' => 'Escritura Pública con Sello de RPP',
        'aval_predial' => 'Boleta Predial del Inmueble en Garantía (Fehaciente)',
        'aval_libertad_gravamen' => 'Libertad de Gravamen del Inmueble en Garantía',
        'aval_acta_matrimonio' => 'Acta de Matrimonio (si aparece casado en la escritura)',
        'aval_id_conyuge' => 'Identificación Oficial del Cónyuge (si bienes mancomunados)',
    ];

    const PAGARE = [
        'pagare' => 'Pagaré',
    ];

    // Cuota de investigación — requisito general del expediente, no depende
    // del tipo de garantía (lista oficial, 2026-09-22): $3,500 MXN CDMX, no
    // reembolsables. 2026-09-24: ya no la sube el cliente — el asesor
    // confirma el pago en el CRM y el recibo se genera y descarga desde el
    // Portal (ver InvestigacionReceiptGeneratorService).
    const PAGO_INVESTIGACION = [
        'pago_investigacion' => 'Pago de la investigación de arrendamiento ($3,500 MXN en CDMX, no reembolsable)',
    ];

    /** Documentos e información que se le piden al inquilino, sin importar el tipo de garantía. */
    public static function clientFacing(): array
    {
        return self::IDENTIFICACION + self::DATOS_LABORALES + self::INGRESOS + self::REFERENCIAS + self::CREDITO + self::PAGO_INVESTIGACION;
    }

    /**
     * Mensaje de WhatsApp con el checklist (2026-09-21) — en vez de mandar
     * la lista completa como texto plano, enlaza a la página pública con la
     * marca de Home del Valle (public.requisitos-renta) para que se vea
     * presentable. Sin integración real de WhatsApp Business conectada: es
     * un link wa.me con el mensaje precargado, igual que el resto del CRM.
     */
    public static function whatsappMessage(string $nombre, ?string $portalUrl = null): string
    {
        $firstName = explode(' ', trim($nombre))[0] ?: 'Hola';
        $requisitosUrl = route('landing.rentar.requisitos');

        $mensaje = "Hola {$firstName}, soy de Home del Valle. Aquí están los requisitos para avanzar tu proceso de renta: {$requisitosUrl}";

        $mensaje .= $portalUrl
            ? "\n\nSúbelos directamente en tu portal: {$portalUrl}"
            : "\n\nCualquier duda, con gusto te ayudamos.";

        return $mensaje;
    }

    /** URL wa.me lista para usar, o null si el teléfono no es válido. */
    public static function whatsappUrl(?string $phone, string $nombre, ?string $portalUrl = null): ?string
    {
        $digits = self::normalizedPhone($phone);
        if (!$digits) {
            return null;
        }

        return 'https://wa.me/' . $digits . '?text=' . urlencode(self::whatsappMessage($nombre, $portalUrl));
    }

    public static function normalizedPhone(?string $phone): ?string
    {
        if (!$phone) {
            return null;
        }
        $digits = preg_replace('/[^0-9]/', '', $phone);
        return strlen($digits) >= 10 ? $digits : null;
    }
}
