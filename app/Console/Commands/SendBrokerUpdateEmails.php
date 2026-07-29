<?php

namespace App\Console\Commands;

use App\Models\Broker;
use App\Models\CustomEmailTemplate;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Envío puntual (no programado) a los Brokers Externos YA activos, para
 * empezar a medir quién mantiene su ficha actualizada. Reutiliza el mismo
 * template "verificacion-broker" — el link apunta a la actualización de su
 * propio registro, no a una verificación de alta.
 */
class SendBrokerUpdateEmails extends Command
{
    protected $signature = 'app:send-broker-update-emails';
    protected $description = 'Envía el correo de actualización de datos a los Brokers Externos activos que aún no lo han recibido';

    public function handle(): int
    {
        $template = CustomEmailTemplate::where('slug', 'verificacion-broker')
            ->where('status', 'published')
            ->first();

        if (!$template) {
            $this->error('No se encontró la plantilla "verificacion-broker" publicada en /admin/email/custom-templates.');
            return self::FAILURE;
        }

        $brokers = Broker::where('status', 'active')
            ->whereNotNull('email')
            ->whereNull('verification_sent_at')
            ->get();

        $enviados = 0;

        foreach ($brokers as $broker) {
            $token = Str::random(48);
            $link = route('broker-self-update.show', $token);

            try {
                $template->send($broker->email, [
                    'nombre'            => $broker->name,
                    'link_verificacion' => $link,
                ], $broker);

                $broker->update([
                    'verification_token'    => $token,
                    'verification_sent_at'  => now(),
                ]);
                $enviados++;
                $this->line("  {$broker->name}: correo de actualización enviado.");
            } catch (\Throwable $e) {
                $this->warn("  {$broker->name}: fallo al enviar — {$e->getMessage()}");
            }
        }

        $this->info("Enviados: {$enviados} de {$brokers->count()} brokers activos.");

        return self::SUCCESS;
    }
}
