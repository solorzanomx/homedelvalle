<?php

namespace App\Support;

use App\Models\Post;
use App\Models\SiteSetting;

/**
 * Arma el link de WhatsApp prellenado por artículo (Fase 3 del prompt de leads del blog): mismo
 * número que ya usa el resto del sitio, mensaje con el título del post y un código de referencia
 * corto para que Alejandro sepa de qué artículo viene la conversación con solo leerla.
 * Usado por el CTA inline (link puro) y por CtaCapture (después de enviar el form).
 */
class BlogWhatsapp
{
    public static function urlFor(?Post $post, string $messageTemplate): ?string
    {
        $settings = SiteSetting::select('whatsapp_number', 'contact_phone')->first();
        $number = $settings?->whatsapp_number ?: $settings?->contact_phone;
        $digits = $number ? preg_replace('/[^0-9]/', '', $number) : null;
        if (! $digits) {
            return null;
        }
        $digits = strlen($digits) === 10 ? '52' . $digits : $digits;

        $message = str_replace('{titulo}', $post?->title ?? '', $messageTemplate) . ' (ref: p' . ($post?->id ?? 0) . ')';

        return 'https://wa.me/' . $digits . '?text=' . rawurlencode($message);
    }
}
