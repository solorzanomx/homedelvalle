<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Models\Notification;
use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckUncontactedLeads extends Command
{
    protected $signature = 'leads:check-uncontacted';

    protected $description = 'Alerta proactiva cuando un lead lleva más de 60 min sin ninguna interacción registrada (ver docs/07-FLUJO-CAPTACION-Y-MEJORAS.md sección 1)';

    private const SLA_MINUTES = 60;
    private const ALERT_TYPE  = 'lead_uncontacted_alert';

    public function handle(): int
    {
        $stale = Client::where('created_at', '<', now()->subMinutes(self::SLA_MINUTES))
            ->whereDoesntHave('interactions')
            ->get();

        $notified = 0;

        foreach ($stale as $client) {
            $alreadyNotified = Notification::where('type', self::ALERT_TYPE)
                ->where('data->client_id', $client->id)
                ->exists();

            if ($alreadyNotified) {
                continue;
            }

            $recipients = $client->assigned_user_id
                ? User::where('id', $client->assigned_user_id)->get()
                : User::where('role', '!=', 'client')->get();

            foreach ($recipients as $user) {
                Notification::create([
                    'user_id' => $user->id,
                    'type'    => self::ALERT_TYPE,
                    'title'   => 'Lead sin contactar',
                    'body'    => "{$client->name} lleva más de " . self::SLA_MINUTES . " minutos sin contacto — contáctalo cuanto antes.",
                    'data'    => ['url' => route('clients.show', $client), 'client_id' => $client->id],
                ]);

                if ($user->whatsapp || $user->phone) {
                    try {
                        app(WhatsAppService::class)->send(
                            $user->whatsapp ?? $user->phone,
                            "Lead sin contactar: {$client->name} lleva más de " . self::SLA_MINUTES . " min esperando. Contáctalo cuanto antes."
                        );
                    } catch (\Throwable $e) {
                        Log::warning('CheckUncontactedLeads: WhatsApp failed: ' . $e->getMessage());
                    }
                }
            }

            $notified++;
        }

        $this->info("Done. {$notified} leads sin contactar notificados.");

        return Command::SUCCESS;
    }
}
