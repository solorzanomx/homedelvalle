<?php

namespace App\Console\Commands;

use App\Models\Broker;
use App\Models\CustomEmailTemplate;
use App\Models\FormSubmission;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * A los 2 días de que un posible broker escribe por EasyBroker (sin que
 * Alejandro/Ana Laura ya lo hayan trabajado a mano), se le manda el correo
 * de verificación — o, si ya existe en Brokers Externos por correo/teléfono,
 * se salta sin mandarle nada (no molestar dos veces al mismo broker).
 */
class SendBrokerVerificationEmails extends Command
{
    protected $signature = 'app:send-broker-verification-emails {--lead= : Forzar el envío a un lead específico por ID, ignorando la espera de 2 días}';
    protected $description = 'Envía el correo de verificación a posibles brokers de EasyBroker con 2+ días sin trabajar';

    public function handle(): int
    {
        $template = CustomEmailTemplate::where('slug', 'verificacion-broker')
            ->where('status', 'published')
            ->first();

        if (!$template) {
            $this->error('No se encontró la plantilla "verificacion-broker" publicada en /admin/email/custom-templates.');
            return self::FAILURE;
        }

        if ($leadId = $this->option('lead')) {
            $candidatos = FormSubmission::where('id', $leadId)
                ->whereNull('payload->broker_verification_sent_at')
                ->whereNull('payload->broker_verification_skipped_at')
                ->whereNotNull('email')
                ->get();
        } else {
            $candidatos = FormSubmission::where('lead_tag', 'LEAD_BROKER')
                ->where('status', 'new')
                ->where('created_at', '<=', now()->subDays(2))
                ->whereNull('payload->broker_verification_sent_at')
                ->whereNull('payload->broker_verification_skipped_at')
                ->whereNotNull('email')
                ->get();
        }

        $enviados = 0;
        $saltados = 0;

        foreach ($candidatos as $lead) {
            $payload = $lead->payload ?? [];

            $yaExiste = Broker::where('email', $lead->email)
                ->when($lead->phone && $lead->phone !== 'sin teléfono', fn ($q) => $q->orWhere('phone', $lead->phone))
                ->first();

            if ($yaExiste) {
                $payload['broker_verification_skipped_at'] = now()->toDateTimeString();
                $payload['broker_verification_skipped_reason'] = 'ya_existe_en_brokers';
                $payload['matched_broker_id'] = $yaExiste->id;
                FormSubmission::withoutEvents(fn () => $lead->update(['payload' => $payload]));
                $saltados++;
                $this->line("  {$lead->full_name}: ya existe como broker #{$yaExiste->id}, sin enviar correo.");
                continue;
            }

            $token = Str::random(48);
            $link = route('broker-verification.show', $token);

            try {
                $template->send($lead->email, [
                    'nombre'            => $lead->full_name,
                    'link_verificacion' => $link,
                ], $lead);

                $payload['broker_verification_token'] = $token;
                $payload['broker_verification_sent_at'] = now()->toDateTimeString();
                FormSubmission::withoutEvents(fn () => $lead->update(['payload' => $payload]));
                $enviados++;
                $this->line("  {$lead->full_name}: correo de verificación enviado.");
            } catch (\Throwable $e) {
                $this->warn("  {$lead->full_name}: fallo al enviar — {$e->getMessage()}");
            }
        }

        $this->info("Enviados: {$enviados} | Saltados (ya existían): {$saltados}.");

        return self::SUCCESS;
    }
}
