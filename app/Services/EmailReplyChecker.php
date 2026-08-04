<?php

namespace App\Services;

use App\Models\AutomationEnrollment;
use App\Models\Client;
use App\Models\EmailSetting;
use App\Models\Interaction;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Webklex\PHPIMAP\ClientManager;

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

    /**
     * Revisa la bandeja de entrada por correos no leidos de clientes conocidos,
     * los registra como respuesta y detiene cualquier automatizacion activa de ese cliente.
     */
    public function checkReplies(): array
    {
        $stats = ['checked' => 0, 'matched' => 0, 'unmatched' => 0, 'error' => null];

        $settings = EmailSetting::first();
        if (!$settings || !$settings->imap_enabled || !$settings->imap_host || !$settings->imap_username) {
            return $stats;
        }

        try {
            $client = $this->makeClient($settings);
            $client->connect();

            $inbox = $client->getFolder('INBOX');
            $messages = $inbox->query()->unseen()->get();

            foreach ($messages as $imapMessage) {
                $stats['checked']++;

                $from = $imapMessage->getFrom();
                $fromEmail = ($from && count($from)) ? strtolower(trim((string) $from[0]->mail)) : null;

                if (!$fromEmail) {
                    continue;
                }

                $clientModel = Client::whereRaw('LOWER(email) = ?', [$fromEmail])->first();

                if (!$clientModel) {
                    $stats['unmatched']++;
                    continue;
                }

                $stats['matched']++;
                $this->recordReply($clientModel, $imapMessage);
            }

            $settings->update(['imap_last_checked_at' => now()]);
            $client->disconnect();
        } catch (\Throwable $e) {
            Log::error('EmailReplyChecker: ' . $e->getMessage());
            $stats['error'] = $e->getMessage();
        }

        return $stats;
    }

    private function recordReply(Client $clientModel, $imapMessage): void
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

    private function makeClient(EmailSetting $settings)
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
