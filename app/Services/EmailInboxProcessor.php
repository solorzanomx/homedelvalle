<?php

namespace App\Services;

use App\Models\EmailSetting;
use App\Models\ImapProcessedMessage;
use Illuminate\Support\Facades\Log;

/**
 * Unica conexion IMAP por corrida del scheduler (comando email:check-replies,
 * cada 5 min). Recorre correos recientes de la bandeja y, por cada uno no
 * procesado todavia (ledger imap_processed_messages, por Message-ID — no
 * usamos el flag \Seen de IMAP porque la libreria hace fetch en modo PEEK
 * por defecto y ademas ensuciaria el Gmail real del dueno de la cuenta),
 * decide a donde va:
 *   1. Viene de usuarios.inmuebles24.com  -> Inmuebles24LeadImporter
 *   2. Viene del email de un Client conocido -> EmailReplyChecker (respuesta)
 *   3. Cualquier otra cosa -> se marca 'skipped' para no reevaluarla siempre
 */
class EmailInboxProcessor
{
    public function __construct(
        private EmailReplyChecker $replyChecker,
        private Inmuebles24LeadImporter $i24Importer,
    ) {}

    public function run(): array
    {
        $stats = [
            'checked' => 0, 'client_replies' => 0, 'inmuebles24_leads' => 0,
            'skipped' => 0, 'error' => null,
        ];

        $settings = EmailSetting::first();
        if (!$settings || !$settings->imap_enabled || !$settings->imap_host || !$settings->imap_username) {
            return $stats;
        }

        try {
            $client = $this->replyChecker->makeClient($settings);
            $client->connect();

            $inbox = $client->getFolder('INBOX');
            // Ultimos 14 dias: suficiente para no perder nada, acotado para
            // no recorrer toda la bandeja historica en cada corrida.
            $messages = $inbox->query()->since(now()->subDays(14))->leaveUnread()->get();

            foreach ($messages as $imapMessage) {
                $messageId = null;
                try {
                    $messageId = (string) ($imapMessage->getMessageId() ?? '');
                    if ($messageId === '' || ImapProcessedMessage::where('message_id', $messageId)->exists()) {
                        continue;
                    }

                    $stats['checked']++;

                    $fromEmail = $this->firstFromEmail($imapMessage->getFrom());

                    if ($fromEmail && $this->i24Importer->looksLikeInmuebles24Lead($fromEmail)) {
                        $this->handleInmuebles24($imapMessage, $messageId, $stats);
                        continue;
                    }

                    $clientModel = $fromEmail ? $this->replyChecker->findClientByEmail($fromEmail) : null;

                    if ($clientModel) {
                        $this->replyChecker->recordReply($clientModel, $imapMessage);
                        ImapProcessedMessage::create(['message_id' => $messageId, 'type' => 'client_reply']);
                        $stats['client_replies']++;
                        continue;
                    }

                    ImapProcessedMessage::create(['message_id' => $messageId, 'type' => 'skipped']);
                    $stats['skipped']++;
                } catch (\Throwable $e) {
                    // Un correo individual con formato raro (encabezado "De:"
                    // que la libreria no pudo parsear como direccion RFC,
                    // adjunto corrupto, etc.) ya NO tumba la corrida completa
                    // (bug real 2026-09-21: un solo mensaje asi dejaba en cero
                    // TODA la importacion, incluidos los leads de Inmuebles24
                    // que si venian bien formados en el mismo lote).
                    Log::error('EmailInboxProcessor: fallo procesando un mensaje, se omite y se sigue con el resto: ' . $e->getMessage(), [
                        'message_id' => $messageId,
                    ]);
                    $stats['message_errors'] = ($stats['message_errors'] ?? 0) + 1;
                }
            }

            $settings->update(['imap_last_checked_at' => now()]);
            $client->disconnect();
        } catch (\Throwable $e) {
            Log::error('EmailInboxProcessor: ' . $e->getMessage());
            $stats['error'] = $e->getMessage();
        }

        return $stats;
    }

    /**
     * getFrom() de webklex/php-imap normalmente regresa una coleccion
     * Countable de direcciones — pero cuando el encabezado "From" no se
     * pudo parsear como direccion RFC estandar (formato raro de un
     * remitente automatizado), regresa un solo objeto Attribute que NO es
     * Countable. El count($from) original tronaba con un TypeError ahi,
     * tumbando la corrida completa (bug real detectado 2026-09-21).
     */
    private function firstFromEmail($from): ?string
    {
        if (!$from) {
            return null;
        }

        if ($from instanceof \Countable || is_array($from)) {
            $first = count($from) > 0 ? $from[0] : null;
        } else {
            $first = $from;
        }

        return isset($first->mail) ? strtolower(trim((string) $first->mail)) : null;
    }

    private function handleInmuebles24($imapMessage, string $messageId, array &$stats): void
    {
        $subject = (string) ($imapMessage->getSubject() ?? '');
        $html = (string) ($imapMessage->getHTMLBody() ?: '');

        $parsed = $this->i24Importer->parse($subject, $html);

        if (!$parsed) {
            ImapProcessedMessage::create(['message_id' => $messageId, 'type' => 'skipped']);
            $stats['skipped']++;
            return;
        }

        if ($this->i24Importer->alreadyImported($parsed['ref'], $parsed['email'])) {
            ImapProcessedMessage::create(['message_id' => $messageId, 'type' => 'inmuebles24_lead']);
            return;
        }

        $this->i24Importer->import($parsed);
        ImapProcessedMessage::create(['message_id' => $messageId, 'type' => 'inmuebles24_lead']);
        $stats['inmuebles24_leads']++;
    }
}
