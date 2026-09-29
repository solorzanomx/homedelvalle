<?php
namespace App\Listeners;

use App\Events\FormSubmitted;
use App\Helpers\MailConfigurator;
use App\Mail\V4\Data\AcuseData;
use App\Mail\V4\Mailables\AcuseMail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class SendAcuseMail {
    public function handle(FormSubmitted $event): void {
        $submission = $event->submission;

        // El CTA del blog (Fase 3 del prompt de leads) captura solo WhatsApp — sin email no hay a
        // quién mandarle un acuse por correo (antes TODOS los formularios exigían email, así que
        // este caso nunca ocurría; Mail::to(null)->send() no está garantizado a fallar en silencio).
        if (empty($submission->email)) {
            return;
        }

        $cacheKey = 'acuse_sent_' . $event->submission->id;
        if (Cache::has($cacheKey)) return;
        Cache::put($cacheKey, true, now()->addMinutes(10));

        MailConfigurator::applyGlobalSettings();

        Mail::to($submission->email)->send(
            new AcuseMail(new AcuseData(
                folio:     (string) $submission->id,
                email:     $submission->email,
                form_type: $submission->form_type,
                nombre:    $submission->full_name,
                payload:   $submission->payload ?? [],
            ))
        );
    }
}
