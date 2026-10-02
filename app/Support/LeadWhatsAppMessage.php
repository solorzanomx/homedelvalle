<?php

namespace App\Support;

use App\Models\FormSubmission;
use App\Models\Property;

/**
 * Arma el mensaje de WhatsApp contextual para un lead — extraído de la vista
 * admin/form-submissions/show.blade.php (donde vivía inline) para poder reusarlo también desde
 * Admin\FormSubmissionController::whatsappRedirect() (2026-10-02: el botón "Responder por
 * WhatsApp" era un link plano sin esto, el redirect server-side lo necesita para armar la URL).
 */
class LeadWhatsAppMessage
{
    public static function build(FormSubmission $lead): string
    {
        $payload = $lead->payload ?? [];
        $nombreCorto = explode(' ', trim($lead->full_name))[0] ?: 'Hola';

        $propiedadLocal = !empty($payload['propiedad_local_id'])
            ? Property::find($payload['propiedad_local_id'])
            : null;

        $mensaje = match (true) {
            (bool) $propiedadLocal => "Hola {$nombreCorto}, soy de Home del Valle. Vi tu interés en «{$propiedadLocal->title}» ($" . number_format((float) $propiedadLocal->price) . " {$propiedadLocal->currency}). Sigue disponible — ¿te gustaría agendar una visita esta semana?",

            !empty($payload['eb_titulo']) => "Hola {$nombreCorto}, soy de Home del Valle. Vi tu interés en «{$payload['eb_titulo']}»"
                . (!empty($payload['eb_precio']) ? " ({$payload['eb_precio']}" . (($payload['eb_operacion'] ?? null) === 'renta' ? ' de renta' : '') . ')' : '')
                . '. ¿Te gustaría agendar una visita esta semana?',

            !empty($payload['eb_property_id']) => "Hola {$nombreCorto}, soy de Home del Valle. Vi tu interés en la propiedad {$payload['eb_property_id']} — con gusto te comparto los detalles. ¿Qué estás buscando: comprar o rentar?",

            !empty($payload['titulo_aviso']) => "Hola {$nombreCorto}, soy de Home del Valle. Vi tu interés en «{$payload['titulo_aviso']}»"
                . (!empty($payload['precio']) ? " ({$payload['precio']})" : '')
                . '. ¿Te gustaría agendar una visita esta semana?',

            in_array($lead->form_type, ['vendedor', 'vendedor_predio'], true) =>
                "Hola {$nombreCorto}, soy de Home del Valle. Recibimos tu solicitud de valuación — ¿tienes 5 minutos para platicar de tu propiedad?",

            default => "Hola {$nombreCorto}, te contactamos de Home del Valle sobre tu solicitud. ¿En qué horario te queda bien platicar?",
        };

        // Si la IA ya redactó la respuesta, gana sobre todo lo anterior — mismo criterio que la
        // vista original.
        return $payload['ai_respuesta'] ?? $mensaje;
    }
}
