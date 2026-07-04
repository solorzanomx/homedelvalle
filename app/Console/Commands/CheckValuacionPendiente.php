<?php

namespace App\Console\Commands;

use App\Models\Captacion;
use App\Models\Notification;
use App\Models\Task;
use App\Services\WhatsAppService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckValuacionPendiente extends Command
{
    protected $signature = 'captaciones:check-valuacion-pendiente';

    protected $description = 'Recuerda al broker dar seguimiento cuando un propietario recibió su valuación y lleva días sin confirmar el precio (etapa2_completed_at sin etapa3_completed_at)';

    private const DAYS_THRESHOLD = 3;
    private const ALERT_TYPE     = 'valuacion_pendiente_respuesta';

    /**
     * El stage de Operation no distingue "esperando decisión de precio" de
     * "esperando firma de exclusiva" — ambos casos viven en stage='exclusiva'
     * (CaptacionService::ETAPA_TO_STAGE). La distinción real vive en el
     * propio Captacion: etapa2_completed_at (valuación vinculada/visible)
     * sin etapa3_completed_at (cliente aún no confirma) — cubre tanto "el
     * broker aún no propone precio" como "ya lo propuso y no responde".
     */
    public function handle(): int
    {
        $pending = Captacion::with(['client', 'operation.user'])
            ->whereNotNull('etapa2_completed_at')
            ->whereNull('etapa3_completed_at')
            ->whereNull('declined_at')
            ->where('etapa2_completed_at', '<', now()->subDays(self::DAYS_THRESHOLD))
            ->get();

        $notified = 0;

        foreach ($pending as $captacion) {
            $broker = $captacion->operation?->user;
            if (!$broker) {
                continue;
            }

            $alreadyNotifiedToday = Notification::where('type', self::ALERT_TYPE)
                ->where('data->captacion_id', $captacion->id)
                ->where('created_at', '>=', now()->startOfDay())
                ->exists();

            if ($alreadyNotifiedToday) {
                continue;
            }

            $clientName = $captacion->client?->name ?? 'El propietario';
            $days       = (int) $captacion->etapa2_completed_at->diffInDays(now());
            $title      = "Dar seguimiento: {$clientName} no ha respondido su valuación";

            Notification::create([
                'user_id' => $broker->id,
                'type'    => self::ALERT_TYPE,
                'title'   => 'Valuación sin respuesta',
                'body'    => "{$clientName} recibió su valuación hace {$days} días y no ha confirmado el precio — dale seguimiento.",
                'data'    => ['url' => route('admin.captaciones.show', $captacion), 'captacion_id' => $captacion->id],
            ]);

            $alreadyHasTaskToday = Task::where('client_id', $captacion->client_id)
                ->whereDate('created_at', today())
                ->where('title', $title)
                ->exists();

            if (!$alreadyHasTaskToday) {
                Task::create([
                    'user_id'     => $broker->id,
                    'client_id'   => $captacion->client_id,
                    'operation_id' => $captacion->operation_id,
                    'title'       => $title,
                    'description' => 'Lleva ' . $days . ' días sin responder desde que recibió la valuación. '
                        . 'Recomendación: llamar personalmente en vez de mandar otro mensaje — el silencio después '
                        . 'de un precio más bajo de lo esperado casi nunca es un "no", suele ser que el número dolió. '
                        . 'Reencuadra la conversación hacia el estado real de su inmueble y la estrategia de promoción, '
                        . 'no hacia el edificio en general.',
                    'priority'    => 'alta',
                    'status'      => 'pending',
                    'due_date'    => today(),
                ]);
            }

            if ($broker->whatsapp || $broker->phone) {
                try {
                    app(WhatsAppService::class)->send(
                        $broker->whatsapp ?? $broker->phone,
                        "{$clientName} lleva {$days} días sin confirmar el precio de su valuación. Dale seguimiento cuanto antes."
                    );
                } catch (\Throwable $e) {
                    Log::warning('CheckValuacionPendiente: WhatsApp failed: ' . $e->getMessage());
                }
            }

            $notified++;
        }

        $this->info("Done. {$notified} recordatorios de valuación pendiente enviados.");

        return Command::SUCCESS;
    }
}
