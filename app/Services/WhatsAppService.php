<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    /**
     * Send a WhatsApp message.
     * This is a mock implementation — replace with actual API (Twilio, Meta, etc.)
     */
    public function send(string $phone, string $message, array $options = []): array
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (strlen($phone) < 10) {
            return ['success' => false, 'error' => 'Invalid phone number'];
        }

        // In production: integrate with Twilio/Meta WhatsApp Business API
        Log::info("WhatsAppService [MOCK]: Sending to {$phone}", [
            'message' => mb_substr($message, 0, 100),
            'full_length' => mb_strlen($message),
        ]);

        // Simulate success
        return [
            'success' => true,
            'message_id' => 'wa_mock_' . uniqid(),
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
