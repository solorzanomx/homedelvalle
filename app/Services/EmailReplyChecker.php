<?php

namespace App\Services;

use App\Models\AutomationEnrollment;
use App\Models\Client;
use App\Models\EmailSetting;
use App\Models\Interaction;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Str;
use Webklex\PHPIMAP\ClientManager;

/**
 * Se encarga UNICAMENTE de: dado un mensaje IMAP ya identificado como venido
 * de un Client conocido, registrar la respuesta y detener sus automations.
 * La conexion IMAP y el recorrido de la bandeja viven en EmailInboxProcessor
 * (que tambien delega a Inmuebles24LeadImporter) — asi solo hay UNA conexion
 * y UN recorrido de mensajes por corrida del scheduler.
 */
class EmailReplyChecker
{
    public function testConnection(): array
    {
        $settings = EmailSetting::first();

        if (!$settings || !$settings->imap_host || !$settings->imap_username) {
            return ['success' => false, 'message' => 'IMAP no configurado.'];
        }

        try {
            $client = $this->makeClient($settings);
            $client->connect();
            $client->disconnect();

            return ['success' => true, 'message' => 'Conexion IMAP exitosa con ' . $settings->imap_username];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    public function findClientByEmail(string $email): ?Client
    {
        return Client::whereRaw('LOWER(email) = ?', [strtolower($email)])->first();
    }

    public function recordReply(Client $clientModel, $imapMessage): void
    {
        $subject = (string) ($imapMessage->getSubject() ?? '(sin asunto)');
        $rawBody = (string) ($imapMessage->getTextBody() ?: $imapMessage->getHTMLBody() ?: '');
        $bodySnippet = Str::limit(trim(strip_tags($rawBody)), 500);

        // Marca el ultimo correo saliente sin respuesta como respondido
        $lastOutbound = Message::where('client_id', $clientModel->id)
            ->where('channel', 'email')
            ->where('direction', 'outbound')
            ->whereNull('replied_at')
            ->latest('sent_at')
            ->first();
        $lastOutbound?->markReplied();

        Message::create([
            'client_id' => $clientModel->id,
            'channel' => 'email',
            'direction' => 'inbound',
            'subject' => $subject,
            'body' => $bodySnippet,
            'status' => 'replied',
            'sent_at' => now(),
        ]);

        $userId = $clientModel->assigned_user_id
            ?? User::where('role', 'admin')->value('id')
            ?? User::value('id');

        Interaction::create([
            'client_id' => $clientModel->id,
            'user_id' => $userId,
            'type' => 'email',
            'description' => 'Respuesta recibida por correo: "' . $subject . '" — ' . $bodySnippet,
            'completed_at' => now(),
        ]);

        // El cliente ya respondio: no tiene caso seguir con la cadencia de "sin respuesta"
        AutomationEnrollment::where('client_id', $clientModel->id)
            ->where('status', 'active')
            ->get()
            ->each(fn (AutomationEnrollment $e) => $e->markCompleted());
    }

    public function makeClient(EmailSetting $settings)
    {
        $manager = new ClientManager();

        return $manager->make([
            'host' => $settings->imap_host,
            'port' => $settings->imap_port ?: 993,
            'encryption' => $settings->imap_encryption ?: 'ssl',
            'validate_cert' => true,
            'username' => $settings->imap_username,
            'password' => $settings->imap_password,
            'protocol' => 'imap',
        ]);
    }
}
