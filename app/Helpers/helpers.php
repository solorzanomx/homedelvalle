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
