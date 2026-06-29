<?php

use App\Helpers\WhatsAppHelper;

if (! function_exists('getWhatsAppOptions')) {
    /**
     * Obtiene opciones contextuales de WhatsApp para Blade
     */
    function getWhatsAppOptions(): array
    {
        return WhatsAppHelper::getOptions();
    }
}

if (! function_exists('formatMxPhone')) {
    /**
     * Formatea un número telefónico mexicano para visualización.
     * +5215513450978  →  +52 55 1345 0978
     * +525513450978   →  +52 55 1345 0978
     */
    function formatMxPhone(?string $phone): string
    {
        if (! $phone) return '';
        $d = preg_replace('/\D/', '', $phone);
        // 13 dígitos: 52 + 1 (prefijo móvil antiguo) + 2 (lada) + 8 (número)
        if (strlen($d) === 13 && str_starts_with($d, '52')) {
            return '+52 ' . substr($d, 3, 2) . ' ' . substr($d, 5, 4) . ' ' . substr($d, 9, 4);
        }
        // 12 dígitos: 52 + 2 (lada) + 8 (número)
        if (strlen($d) === 12 && str_starts_with($d, '52')) {
            return '+52 ' . substr($d, 2, 2) . ' ' . substr($d, 4, 4) . ' ' . substr($d, 8, 4);
        }
        return $phone;
    }
}
