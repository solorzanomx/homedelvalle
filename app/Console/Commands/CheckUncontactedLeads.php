<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Models\FormSubmission;
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

    /** Leads de formulario/portal aún en 'new' — SLA más laxo (2 h). */
    private const FORM_SLA_MINUTES = 120;
    private const FORM_ALERT_TYPE  = 'form_lead_uncontacted_alert';

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

        $notified += $this->checkFormSubmissions();

        $this->info("Done. {$notified} leads sin contactar notificados.");

        return Command::SUCCESS;
    }

    /**
     * Leads de Leads & Formularios (sitio + portales) que siguen en 'new'
     * pasadas 2 horas. Los leads de portal son carrera de velocidad — a las
     * 24 h ya hablaron con otros asesores. Se excluyen los brokers de
     * colaboración (LEAD_BROKER): no son urgentes.
     */
    private function checkFormSubmissions(): int
    {
        $stale = FormSubmission::where('status', 'new')
            ->whereNull('contacted_at')
            ->where('lead_tag', '!=', 'LEAD_BROKER')
            ->where('created_at', '<', now()->subMinutes(self::FORM_SLA_MINUTES))
            // margen de 7 días: no revivir alertas de leads viejos importados
            ->where('created_at', '>', now()->subDays(7))
            ->get();

        $notified = 0;

        foreach ($stale as $submission) {
            $alreadyNotified = Notification::where('type', self::FORM_ALERT_TYPE)
                ->where('data->form_submission_id', $submission->id)
                ->exists();

            if ($alreadyNotified) {
                continue;
            }

            $recipients = $submission->assigned_to
                ? User::where('id', $submission->assigned_to)->get()
                : User::where('role', '!=', 'client')->get();

            $origen = $submission->form_type === 'easybroker' ? 'portal' : 'sitio';

            foreach ($recipients as $user) {
                Notification::create([
                    'user_id' => $user->id,
                    'type'    => self::FORM_ALERT_TYPE,
                    'title'   => 'Lead de ' . $origen . ' sin atender',
                    'body'    => "{$submission->full_name} (" . ($submission->form_type === 'easybroker' ? 'EasyBroker' : $submission->form_type) . ") lleva más de 2 horas en 'nuevo' — los leads de {$origen} se enfrían rápido.",
                    'data'    => ['url' => route('admin.form-submissions.show', $submission), 'form_submission_id' => $submission->id],
                ]);
            }

            $notified++;
        }

        return $notified;
    }
}
