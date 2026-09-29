<?php

namespace App\Console\Commands;

use App\Models\BlogLeadReminder;
use App\Models\FormSubmission;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Console\Command;

/**
 * Optimización post-lanzamiento del prompt de leads del blog: recuerda al asesor (o al asignado)
 * dar seguimiento a un lead del blog que sigue "nuevo" (sin contactar) a los 7 y 14 días — antes
 * no existía ningún seguimiento automático para el heredero en "etapa temprana" que no avanzó.
 * Ver docs/funcionalidades/blog-leads-panel.md.
 */
class RemindStaleBlogLeads extends Command
{
    protected $signature = 'blog:remind-stale-leads';
    protected $description = 'Notifica al asesor sobre leads del blog sin contactar a los 7 y 14 días';

    private const TIERS = ['d7' => 7, 'd14' => 14];

    public function handle(): int
    {
        $notified = 0;

        foreach (self::TIERS as $tier => $days) {
            // "al menos N días" (no "exactamente N") a propósito: si el comando no corre un día
            // (mantenimiento, falla), se pone al corriente solo — el índice único en
            // (form_submission_id, tier) es lo que evita mandar el mismo recordatorio dos veces.
            $leads = FormSubmission::where('status', 'new')
                ->where('created_at', '<=', now()->subDays($days))
                ->get()
                ->filter(fn ($lead) => str_starts_with($lead->payload['origen'] ?? '', 'blog_'));

            foreach ($leads as $lead) {
                if (BlogLeadReminder::where('form_submission_id', $lead->id)->where('tier', $tier)->exists()) {
                    continue;
                }

                $this->notify($lead, $days);
                BlogLeadReminder::create(['form_submission_id' => $lead->id, 'tier' => $tier, 'created_at' => now()]);
                $notified++;
            }
        }

        $this->info("Recordatorios enviados: {$notified}");

        return self::SUCCESS;
    }

    private function notify(FormSubmission $lead, int $days): void
    {
        $recipients = $lead->assigned_to
            ? User::where('id', $lead->assigned_to)->get()
            : User::whereIn('role', ['admin', 'super_admin'])->get();

        $title = "Lead del blog sin contactar ({$days} días)";
        $body = $lead->full_name . ' · ' . ($lead->phone ?: 'sin teléfono') . ($lead->source_page ? ' · ' . $lead->source_page : '');

        foreach ($recipients as $user) {
            Notification::create([
                'user_id' => $user->id,
                'type' => 'blog_lead_reminder',
                'title' => $title,
                'body' => $body,
                'data' => ['url' => '/admin/form-submissions/' . $lead->id, 'form_submission_id' => $lead->id],
            ]);
        }
    }
}
