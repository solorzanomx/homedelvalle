<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Document;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

/**
 * Avisa al cliente que uno o varios documentos fueron rechazados, con el
 * motivo y el enlace directo a su Portal (2026-09-25).
 *
 * - UN solo correo por cliente con todos sus documentos rechazados (no uno por
 *   documento): al rechazar varios en una sesión, se espera unos minutos
 *   (DEFAULT_DELAY_MINUTES) y el scheduler los junta. Sin queue worker en
 *   producción, el "debounce" es el propio scheduler.
 * - El asesor puede adelantarse ("Avisar ahora") o usar WhatsApp (enlace wa.me,
 *   no hay integración real de WhatsApp Business todavía).
 * - Estado en documents.rejection_notified_at / _via: email, whatsapp,
 *   skipped (el asesor pidió no avisar), no_email, failed.
 */
class DocumentRejectionNotifier
{
    const DEFAULT_DELAY_MINUTES = 10;

    /** Estados que el asesor todavía puede (re)enviar a mano. */
    const RESENDABLE_VIA = ['skipped', 'no_email', 'failed'];

    /** Rechazados del cliente que el asesor aún puede avisar. */
    public function pendingFor(Client $client): Collection
    {
        return Document::where('client_id', $client->id)
            ->where('status', 'rejected')
            ->where(fn($q) => $q->whereNull('rejection_notified_at')->orWhereIn('rejection_notified_via', self::RESENDABLE_VIA))
            ->orderBy('rejected_at')
            ->get();
    }

    /** Lo que envía el scheduler: rechazados sin avisar y con la espera cumplida. */
    public function notifyDue(int $delayMinutes = self::DEFAULT_DELAY_MINUTES): int
    {
        $due = Document::with('client')
            ->where('status', 'rejected')
            ->whereNull('rejection_notified_at')
            ->whereNotNull('client_id')
            ->where('rejected_at', '<=', now()->subMinutes($delayMinutes))
            ->get()
            ->groupBy('client_id');

        $clients = 0;
        foreach ($due as $docs) {
            $client = $docs->first()->client;
            if (! $client) {
                continue;
            }
            $this->sendEmail($client, $docs);
            $clients++;
        }

        return $clients;
    }

    const REMINDER_EVERY_DAYS = 3;
    const MAX_REMINDERS = 2;

    /**
     * Recordatorio amable a quien ya fue avisado y no ha vuelto a subir (cada 3
     * días, máximo 2). Tras el 2.º, avisa al asesor para que le llame — el
     * silencio de un cliente que ya sabe qué corregir suele ser fricción, no
     * desinterés. @return array{reminded:int, escalated:int}
     */
    public function remindDue(): array
    {
        $cutoff = now()->subDays(self::REMINDER_EVERY_DAYS);

        $docs = Document::with('client')
            ->where('status', 'rejected')
            ->whereIn('rejection_notified_via', ['email', 'whatsapp'])
            ->where('rejection_reminders_sent', '<', self::MAX_REMINDERS)
            ->where(fn($q) => $q
                ->where(fn($a) => $a->where('rejection_reminders_sent', 0)->where('rejection_notified_at', '<=', $cutoff))
                ->orWhere(fn($b) => $b->where('rejection_reminders_sent', '>', 0)->where('rejection_reminded_at', '<=', $cutoff)))
            ->whereNotNull('client_id')
            ->get()
            ->groupBy('client_id');

        $reminded = 0;
        $escalated = 0;
        foreach ($docs as $group) {
            $client = $group->first()->client;
            if (! $client) {
                continue;
            }

            // Si ya volvió a subir esa categoría (después del rechazo), no hay nada que recordar.
            $pending = $group->reject(fn($d) => Document::where('client_id', $client->id)
                ->where('category', $d->category)
                ->where('id', '!=', $d->id)
                ->where('created_at', '>', $d->rejected_at ?? $d->updated_at)
                ->where('status', '!=', 'rejected')
                ->exists());
            if ($pending->isEmpty()) {
                continue;
            }

            $sent = false;
            if ($client->email) {
                try {
                    $sent = app(EmailService::class)->send($client->email, $this->reminderSubject($pending), $this->emailHtml($client, $pending, true), $client->name);
                } catch (\Throwable $e) {
                    Log::warning('DocumentRejectionNotifier: falló el recordatorio', ['client_id' => $client->id, 'error' => $e->getMessage()]);
                }
            }

            // Se cuenta el intento aunque no haya correo: así el asesor recibe la escalación en vez de esperar en silencio.
            Document::whereIn('id', $pending->pluck('id'))->update([
                'rejection_reminders_sent' => \DB::raw('rejection_reminders_sent + 1'),
                'rejection_reminded_at' => now(),
            ]);
            foreach ($pending as $d) {
                \App\Models\DocumentEvent::log($d, 'reminded', $sent ? 'por correo' : 'sin correo: no se pudo enviar');
            }
            $sent && $reminded++;

            if ((int) $pending->first()->rejection_reminders_sent + 1 >= self::MAX_REMINDERS) {
                $this->escalate($client, $pending);
                $escalated++;
            }
        }

        return ['reminded' => $reminded, 'escalated' => $escalated];
    }

    /** Avisa al asesor: el cliente ya recibió 2 recordatorios y sigue sin corregir. */
    private function escalate(Client $client, Collection $docs): void
    {
        $userId = $client->assigned_user_id;
        if (! $userId) {
            return;
        }
        $labels = $docs->map(fn($d) => $this->label($d))->implode(', ');

        \App\Models\Notification::create([
            'user_id' => $userId,
            'type' => 'documentos_sin_resubir',
            'title' => 'Cliente sin corregir documentos',
            'body' => "{$client->name} no ha vuelto a subir ({$labels}) tras el aviso y 2 recordatorios. Un mensaje o llamada personal suele destrabarlo.",
            'data' => ['url' => route('clients.show', $client->id), 'client_id' => $client->id],
        ]);
    }

    private function reminderSubject(Collection $docs): string
    {
        return $docs->count() === 1 ? 'Te recordamos: falta volver a subir un documento' : 'Te recordamos: faltan ' . $docs->count() . ' documentos por volver a subir';
    }

    /** Envía el correo y marca los documentos. @return bool ¿salió el correo? */
    public function sendEmail(Client $client, Collection $docs, ?User $sender = null): bool
    {
        if ($docs->isEmpty()) {
            return false;
        }
        if (! $client->email) {
            $this->mark($docs, 'no_email');
            return false;
        }

        $sent = false;
        try {
            $sent = app(EmailService::class)->send(
                $client->email,
                $this->subject($docs),
                $this->emailHtml($client, $docs),
                $client->name,
                null,
                $sender,
            );
        } catch (\Throwable $e) {
            Log::warning('DocumentRejectionNotifier: falló el correo', ['client_id' => $client->id, 'error' => $e->getMessage()]);
        }

        $this->mark($docs, $sent ? 'email' : 'failed');

        return $sent;
    }

    /** Enlace wa.me con el mensaje listo; marca los documentos como avisados por WhatsApp. */
    public function whatsappUrl(Client $client, Collection $docs): ?string
    {
        $digits = preg_replace('/[^0-9]/', '', $client->whatsapp ?: $client->phone ?: '');
        if (strlen($digits) < 10 || $docs->isEmpty()) {
            return null;
        }
        if (strlen($digits) === 10) {
            $digits = '52' . $digits; // México
        }

        $this->mark($docs, 'whatsapp');

        return 'https://wa.me/' . $digits . '?text=' . rawurlencode($this->whatsappText($client, $docs));
    }

    public function markSkipped(Document $doc): void
    {
        $doc->update(['rejection_notified_at' => now(), 'rejection_notified_via' => 'skipped']);
        \App\Models\DocumentEvent::log($doc, 'notified', 'no se avisó (decisión del asesor)');
    }

    private function mark(Collection $docs, string $via): void
    {
        Document::whereIn('id', $docs->pluck('id'))->update(['rejection_notified_at' => now(), 'rejection_notified_via' => $via]);
        $labels = ['email' => 'por correo', 'whatsapp' => 'por WhatsApp', 'skipped' => 'no se avisó (decisión del asesor)', 'no_email' => 'sin correo registrado', 'failed' => 'el correo no se pudo enviar'];
        foreach ($docs as $d) {
            \App\Models\DocumentEvent::log($d, 'notified', $labels[$via] ?? $via);
        }
    }

    public function portalUrl(Client $client, Collection $docs): string
    {
        $base = rtrim(config('portal.url', 'https://miportal.homedelvalle.mx'), '/');
        $first = $docs->first();

        if ($first?->captacion_id && Route::has('portal.captacion')) {
            return route('portal.captacion');
        }

        return $base . (Route::has('portal.expediente') ? parse_url(route('portal.expediente'), PHP_URL_PATH) : '/mi-expediente');
    }

    private function firstName(Client $client): string
    {
        return explode(' ', trim($client->name ?? ''))[0] ?: 'Hola';
    }

    private function label(Document $d): string
    {
        return Document::CATEGORIES[$d->category] ?? ($d->label ?: 'Documento');
    }

    private function subject(Collection $docs): string
    {
        return $docs->count() === 1
            ? 'Necesitamos que vuelvas a subir un documento'
            : 'Necesitamos que vuelvas a subir ' . $docs->count() . ' documentos';
    }

    public function whatsappText(Client $client, Collection $docs): string
    {
        $lines = $docs->map(fn($d) => '• ' . $this->label($d) . ($d->rejection_reason ? ': ' . $d->rejection_reason : ''))->implode("\n");
        $url = $this->portalUrl($client, $docs);

        return "Hola {$this->firstName($client)}, soy de Home del Valle. Revisamos tus documentos y necesitamos que vuelvas a subir "
            . ($docs->count() === 1 ? 'este:' : 'estos:') . "\n\n{$lines}\n\n"
            . "Súbelo en tu Portal (si es de tu banco, mejor en PDF): {$url}\n\nCualquier duda, aquí estoy.";
    }

    private function emailHtml(Client $client, Collection $docs, bool $reminder = false): string
    {
        $url = e($this->portalUrl($client, $docs));
        $items = $docs->map(function ($d) {
            $reason = $d->rejection_reason ? '<div style="color:#991b1b;font-size:14px;margin-top:2px;">' . e($d->rejection_reason) . '</div>' : '';
            return '<li style="margin-bottom:12px;"><strong>' . e($this->label($d)) . '</strong>' . $reason . '</li>';
        })->implode('');

        return '<div style="font-family:Arial,Helvetica,sans-serif;font-size:15px;color:#0f172a;line-height:1.5;max-width:560px;">'
            . '<p>Hola ' . e($this->firstName($client)) . ',</p>'
            . ($reminder
                ? '<p>Solo un recordatorio amable: para avanzar con tu trámite todavía necesitamos que vuelvas a subir '
                    . ($docs->count() === 1 ? 'este documento' : 'estos documentos') . ':</p>'
                : '<p>Revisamos lo que subiste a tu Portal y necesitamos que vuelvas a subir '
                    . ($docs->count() === 1 ? 'el siguiente documento' : 'los siguientes documentos') . ':</p>')
            . '<ul style="padding-left:20px;">' . $items . '</ul>'
            . '<p style="margin:24px 0;"><a href="' . $url . '" style="background:#1D4ED8;color:#fff;text-decoration:none;padding:12px 22px;border-radius:8px;font-weight:bold;display:inline-block;">Subir mis documentos</a></p>'
            . '<p style="background:#eff6ff;border-radius:8px;padding:12px 14px;font-size:14px;">💡 <strong>Para que se apruebe a la primera:</strong> si es de tu banco o de un proveedor, descárgalo en <strong>PDF</strong>. Si es foto, que sea plana, con buena luz y completa. Evita fotografiar la pantalla del celular.</p>'
            . '<p>Cualquier duda, responde este correo y te ayudamos.</p>'
            . '<p>Saludos,<br>Home del Valle Bienes Raíces</p></div>';
    }
}
