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

        // In production: integrate with Twilio/Meta WhatsApp Business API
        Log::info("WhatsAppService: Sending to {$phone}", [
            'message' => mb_substr($message, 0, 100),
        ]);

        return [
            'success' => true,
            'message_id' => 'wa_' . uniqid(),
            'phone' => $phone,
        ];
    }

    /**
     * Send a template message (for WhatsApp Business API).
     */
    public function sendTemplate(string $phone, string $templateName, array $parameters = []): array
    {
        Log::info("WhatsAppService [MOCK]: Template '{$templateName}' to {$phone}", $parameters);

        return [
            'success' => true,
            'message_id' => 'wa_tpl_' . uniqid(),
            'template' => $templateName,
        ];
    }
}
