<?php

namespace App\Services;

use App\Models\EmailSetting;
use App\Models\ImapProcessedMessage;
use Illuminate\Support\Facades\Log;

/**
 * Unica conexion IMAP por corrida del scheduler (comando email:check-replies,
 * cada 5 min). Recorre correos recientes de INBOX y de Spam (Gmail suele
 * marcar como spam los correos de leads-notifier de Inmuebles24 — pedido
 * real de Alejandro 2026-09-22 tras confirmar que se le fueron leads ahi) y,
 * por cada uno no procesado todavia (ledger imap_processed_messages, por
 * Message-ID — no usamos el flag \Seen de IMAP porque la libreria hace
 * fetch en modo PEEK por defecto y ademas ensuciaria el Gmail real del
 * dueno de la cuenta), decide a donde va:
 *   1. Viene de usuarios.inmuebles24.com/usuarios.vivanuncios.com.mx -> Inmuebles24LeadImporter
 *   2. Viene del email de un Client conocido -> EmailReplyChecker (respuesta)
 *   3. Cualquier otra cosa -> se marca 'skipped' para no reevaluarla siempre
 */
class EmailInboxProcessor
{
    /**
     * "[Gmail]/Spam" es el path real confirmado para esta cuenta (Gmail con
     * UI en espanol) via $client->getFolders(false) — los nombres de las
     * carpetas especiales de Gmail se localizan segun el idioma de la
     * cuenta, no son un estandar IMAP fijo.
     */
    private const FOLDERS = ['INBOX', '[Gmail]/Spam'];

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

            foreach (self::FOLDERS as $folderPath) {
                try {
                    $folder = $client->getFolder($folderPath);
                    if (!$folder) {
                        continue;
                    }
                    // Ultimos 14 dias: suficiente para no perder nada, acotado para
                    // no recorrer toda la bandeja historica en cada corrida.
                    $messages = $folder->query()->since(now()->subDays(14))->leaveUnread()->get();

                    foreach ($messages as $imapMessage) {
                        $this->processMessage($imapMessage, $stats);
                    }
                } catch (\Throwable $e) {
                    // Que Spam no exista o falle no debe tumbar el procesado
                    // de INBOX (ni viceversa) — cada carpeta es independiente.
                    Log::error("EmailInboxProcessor: fallo revisando la carpeta {$folderPath}: " . $e->getMessage());
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

    private function processMessage($imapMessage, array &$stats): void
    {
        $messageId = null;
        try {
            $messageId = (string) ($imapMessage->getMessageId() ?? '');
            if ($messageId === '' || ImapProcessedMessage::where('message_id', $messageId)->exists()) {
                return;
            }

            $stats['checked']++;

            $fromEmail = $this->firstFromEmail($imapMessage->getFrom());

            if ($fromEmail && $this->i24Importer->looksLikeInmuebles24Lead($fromEmail)) {
                $this->handleInmuebles24($imapMessage, $messageId, $stats, $fromEmail);
                return;
            }

            $clientModel = $fromEmail ? $this->replyChecker->findClientByEmail($fromEmail) : null;

            if ($clientModel) {
                $this->replyChecker->recordReply($clientModel, $imapMessage);
                ImapProcessedMessage::create(['message_id' => $messageId, 'type' => 'client_reply']);
                $stats['client_replies']++;
                return;
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

    /**
     * getFrom() de webklex/php-imap SIEMPRE regresa un Webklex\PHPIMAP\Attribute
     * (verificado en produccion 2026-09-21) — implementa ArrayAccess (se
     * indexa con $from[0]) pero NO Countable ni es un array plano. La
     * primera version de este fix asumia que a veces era una coleccion
     * Countable y a veces un objeto suelto con ->mail directo — ninguna de
     * las dos ramas aplicaba nunca, asi que TODOS los correos (no solo los
     * de encabezado raro) se marcaban 'skipped' sin extraer el remitente,
     * silenciosamente. El fix real: indexar via ArrayAccess, nunca contar.
     */
    private function firstFromEmail($from): ?string
    {
        if (!$from) {
            return null;
        }

        try {
            $first = $from instanceof \ArrayAccess ? ($from[0] ?? null) : $from;
        } catch (\Throwable $e) {
            $first = null;
        }

        return isset($first->mail) ? strtolower(trim((string) $first->mail)) : null;
    }

    private function handleInmuebles24($imapMessage, string $messageId, array &$stats, ?string $fromEmail = null): void
    {
        // getSubject() regresa el header crudo: los fragmentos con acentos/
        // emoji vienen codificados RFC 2047 (=?UTF-8?Q?...?=) mezclados con
        // texto plano en el mismo asunto — sin decodificar, "titulo_aviso"
        // salia con ese codigo en vez del texto real (bug real 2026-09-22,
        // visible en "Aviso que consulto" de la ficha del lead).
        $subject = mb_decode_mimeheader((string) ($imapMessage->getSubject() ?? ''));
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

        $this->i24Importer->import($parsed, $fromEmail);
        ImapProcessedMessage::create(['message_id' => $messageId, 'type' => 'inmuebles24_lead']);
        $stats['inmuebles24_leads']++;
    }
}
