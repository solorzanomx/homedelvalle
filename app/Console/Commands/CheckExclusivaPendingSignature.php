<?php

namespace App\Console\Commands;

use App\Models\Captacion;
use App\Models\Notification;
use App\Models\Operation;
use App\Services\WhatsAppService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckExclusivaPendingSignature extends Command
{
    protected $signature = 'captaciones:check-exclusiva-pending';

    protected $description = 'Recuerda al broker dar seguimiento cuando una captación lleva días en etapa "exclusiva" sin firmarse (ver docs/07-FLUJO-CAPTACION-Y-MEJORAS.md sección 2.5)';

    private const DAYS_THRESHOLD = 3;
    private const ALERT_TYPE     = 'exclusiva_pending_signature';

    public function handle(): int
    {
        $pending = Operation::with(['client', 'user'])
            ->where('type', 'captacion')
            ->where('stage', 'exclusiva')
            ->where('status', 'active')
            ->where('updated_at', '<', now()->subDays(self::DAYS_THRESHOLD))
            ->get();

        $notified = 0;

        foreach ($pending as $operation) {
            if (!$operation->user_id) {
                continue;
            }

            $alreadyNotifiedToday = Notification::where('type', self::ALERT_TYPE)
                ->where('data->operation_id', $operation->id)
                ->where('created_at', '>=', now()->startOfDay())
                ->exists();

            if ($alreadyNotifiedToday) {
                continue;
            }

            $clientName = $operation->client?->name ?? 'El propietario';
            $days       = (int) $operation->updated_at->diffInDays(now());
            $captacion  = Captacion::where('operation_id', $operation->id)->first();

            Notification::create([
                'user_id' => $operation->user_id,
                'type'    => self::ALERT_TYPE,
                'title'   => 'Acuerdo de Representación pendiente de firma',
                'body'    => "{$clientName} lleva {$days} días sin firmar el Acuerdo de Representación — dale seguimiento.",
                'data'    => ['url' => $captacion ? route('admin.captaciones.show', $captacion) : null, 'operation_id' => $operation->id],
            ]);

            $broker = $operation->user;
            if ($broker && ($broker->whatsapp || $broker->phone)) {
                try {
                    app(WhatsAppService::class)->send(
                        $broker->whatsapp ?? $broker->phone,
                        "{$clientName} lleva {$days} días sin firmar el Acuerdo de Representación. Dale seguimiento cuanto antes."
                    );
                } catch (\Throwable $e) {
                    Log::warning('CheckExclusivaPendingSignature: WhatsApp failed: ' . $e->getMessage());
                }
            }

            $notified++;
        }

        $this->info("Done. {$notified} recordatorios de Acuerdo pendiente enviados.");

        return Command::SUCCESS;
    }
}
