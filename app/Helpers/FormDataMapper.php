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
            default => 'Formulario enviado',
        };
    }

    private static function getOriginLabel(string $formType): string
    {
        return match ($formType) {
            'vendedor' => 'Solicitud de venta',
            'comprador' => 'Búsqueda de propiedad',
            'b2b' => 'Oportunidad B2B',
            'contacto' => 'Contacto web',
            default => 'Formulario',
        };
    }
}
