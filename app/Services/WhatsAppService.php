<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    private bool $isMock;

    public function __construct()
    {
        $this->isMock = !config('services.whatsapp.enabled', false) || config('services.whatsapp.provider', 'mock') === 'mock';
    }

    public function isMock(): bool
    {
        return $this->isMock;
    }

    /**
     * Send a WhatsApp message.
     */
    public function send(string $phone, string $message, array $options = []): array
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (strlen($phone) < 10) {
            return ['success' => false, 'error' => 'Invalid phone number'];
        }

        if ($this->isMock) {
            Log::warning("WhatsAppService [MOCK]: WhatsApp no configurado. Mensaje a {$phone} no enviado.", [
                'message' => mb_substr($message, 0, 100),
            ]);

            return [
                'success' => true,
                'is_mock' => true,
                'message_id' => 'wa_mock_' . uniqid(),
                'phone' => $phone,
            ];
        }

        // No hay ninguna integracion real (Twilio/Meta WhatsApp Business API)
        // implementada todavia — antes esto devolvia success=true sin mandar
        // nada, dejando mensajes marcados como "enviados" en la BD sin haber
        // salido nunca del servidor (bug real encontrado 2026-07-04). Hasta
        // que se conecte un proveedor real, reportar el fallo honestamente
        // en vez de fingir exito.
        Log::error("WhatsAppService: WHATSAPP_ENABLED=true pero no hay ninguna integracion real conectada (Twilio/Meta) — mensaje a {$phone} NO se envio.", [
            'message' => mb_substr($message, 0, 100),
        ]);

        return [
            'success' => false,
            'error' => 'No hay una integracion real de WhatsApp conectada todavia. Configura un proveedor (Twilio/Meta) o usa modo mock.',
            'phone' => $phone,
        ];
    }

    /**
     * Send a template message (for WhatsApp Business API).
     */
    public function sendTemplate(string $phone, string $templateName, array $parameters = []): array
    {
        if ($this->isMock) {
            Log::warning("WhatsAppService [MOCK]: Template '{$templateName}' a {$phone} no enviado.", $parameters);

            return [
                'success' => true,
                'is_mock' => true,
                'message_id' => 'wa_mock_tpl_' . uniqid(),
                'template' => $templateName,
            ];
        }

        Log::error("WhatsAppService: WHATSAPP_ENABLED=true pero no hay ninguna integracion real conectada (Twilio/Meta) — template '{$templateName}' a {$phone} NO se envio.", $parameters);

        return [
            'success' => false,
            'error' => 'No hay una integracion real de WhatsApp conectada todavia. Configura un proveedor (Twilio/Meta) o usa modo mock.',
            'template' => $templateName,
        ];
    }
}
