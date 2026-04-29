<?php

namespace App\Helpers;

use App\Mail\V4\Data\LeadInternoData;
use App\Models\FormSubmission;

class FormDataMapper
{
    public static function toLeadInternoData(FormSubmission $submission): LeadInternoData
    {
        $mensaje = self::extractMessage($submission);
        $origen = self::getOriginLabel($submission->form_type);

        return new LeadInternoData(
            nombre: $submission->full_name,
            email: $submission->email,
            telefono: $submission->phone,
            origen: $origen,
            fecha: $submission->created_at->format('Y-m-d H:i'),
            mensaje: $mensaje
        );
    }

    private static function extractMessage(FormSubmission $submission): string
    {
        $payload = $submission->payload ?? [];

        return match ($submission->form_type) {
            'vendedor' => $payload['motivo'] ?? 'Solicitud de venta',
            'comprador' => $payload['intento'] ?? 'Solicitud de compra',
            'b2b' => is_array($payload['tipo_operacion'] ?? null)
                ? implode(', ', $payload['tipo_operacion'])
                : ($payload['tipo_operacion'] ?? 'Oportunidad B2B'),
            'contacto' => $payload['intento'] ?? 'Consulta general',
            'arrendatario' => self::formatArrendatarioMessage($payload),
            'propietario_renta' => self::formatPropietarioRentaMessage($payload),
            default => 'Formulario enviado',
        };
    }

    private static function getOriginLabel(string $formType): string
    {
        return match ($formType) {
            'vendedor'          => 'Solicitud de venta',
            'comprador'         => 'Búsqueda de propiedad',
            'b2b'               => 'Oportunidad B2B',
            'contacto'          => 'Contacto web',
            'arrendatario'      => 'Búsqueda de renta',
            'propietario_renta' => 'Propietario — poner en renta',
            default             => 'Formulario',
        };
    }

    private static function formatArrendatarioMessage(array $payload): string
    {
        $parts = [];

        if (!empty($payload['tipo_inmueble'])) {
            $tipos = is_array($payload['tipo_inmueble'])
                ? implode(', ', $payload['tipo_inmueble'])
                : $payload['tipo_inmueble'];
            $parts[] = "Tipo: {$tipos}";
        }

        if (!empty($payload['zonas'])) {
            $zonas = is_array($payload['zonas'])
                ? implode(', ', $payload['zonas'])
                : $payload['zonas'];
            $parts[] = "Zonas: {$zonas}";
        }

        if (!empty($payload['renta_mensual'])) {
            $parts[] = "Presupuesto: {$payload['renta_mensual']}";
        }

        if (!empty($payload['recamaras'])) {
            $parts[] = "Recámaras: {$payload['recamaras']}";
        }

        if (!empty($payload['timing'])) {
            $parts[] = "Timing: {$payload['timing']}";
        }

        if (!empty($payload['mascotas']) && $payload['mascotas'] !== 'no') {
            $parts[] = "Mascotas: {$payload['mascotas']}";
        }

        if (!empty($payload['must_have'])) {
            $parts[] = "Must-have: {$payload['must_have']}";
        }

        return !empty($parts) ? implode(' · ', $parts) : 'Búsqueda de renta';
    }

    private static function formatPropietarioRentaMessage(array $payload): string
    {
        $parts = [];

        if (!empty($payload['tipo_propiedad'])) {
            $parts[] = "Tipo: {$payload['tipo_propiedad']}";
        }

        if (!empty($payload['colonia'])) {
            $parts[] = "Colonia: {$payload['colonia']}";
        }

        if (!empty($payload['renta_esperada'])) {
            $parts[] = "Renta esperada: {$payload['renta_esperada']}";
        }

        if (!empty($payload['amueblado'])) {
            $parts[] = "Amueblado: {$payload['amueblado']}";
        }

        if (!empty($payload['administracion'])) {
            $parts[] = "Administración: {$payload['administracion']}";
        }

        if (!empty($payload['timing'])) {
            $parts[] = "Timing: {$payload['timing']}";
        }

        if (!empty($payload['estado_doc'])) {
            $parts[] = "Docs: {$payload['estado_doc']}";
        }

        return !empty($parts) ? implode(' · ', $parts) : 'Inmueble en renta';
    }
}
