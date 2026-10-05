<?php

namespace App\Console\Commands;

use App\Models\Notification;
use App\Models\RentalProcess;
use App\Models\RentalStageLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Cierra solo un trato de renta unos días después de la Entrega — SOLO si el propietario NO
 * contrató administración continua (rental_processes.management_contracted). Sin esto, un trato se
 * quedaba indefinidamente en "Activo" aunque ya no hubiera nada que hacer: el Portal del inquilino
 * seguía mostrando el camino de cierre (apartado/datos/documentos/garantía/contrato/entrega) con un
 * mensaje genérico de "¡Todo en orden!" para alguien que simplemente ya vivía ahí pagando su renta,
 * sin ningún feature real detrás (hallazgo 2026-10-04, a raíz de una pregunta real de Alejandro).
 *
 * Con administración contratada, el trato se queda abierto a propósito: el Portal muestra el panel
 * de "renta activa" (ver TenantRoadmap/portal.journey) mientras dure el contrato.
 */
class CloseRentalsAfterEntrega extends Command
{
    protected $signature = 'rentals:close-after-entrega';

    protected $description = 'Cierra automáticamente los tratos de renta sin administración contratada, unos días después de llegar a la etapa Entrega';

    /** Margen antes de cerrar solo — da tiempo a confirmar que la entrega fue real y a marcar administración si se le olvidó al asesor. */
    private const DAYS_AFTER_ENTREGA = 3;

    public function handle(): int
    {
        $candidates = RentalProcess::where('status', 'active')
            ->where('stage', 'entrega')
            ->where(fn ($q) => $q->whereNull('management_contracted')->orWhere('management_contracted', false))
            ->get();

        $closed = 0;

        foreach ($candidates as $rental) {
            $entregaLog = RentalStageLog::where('rental_process_id', $rental->id)
                ->where('to_stage', 'entrega')
                ->latest('created_at')
                ->first();

            // Sin log de cuándo llegó a "entrega" (dato viejo, migrado a mano, etc.) no sabemos si ya
            // pasó el margen — mejor no tocarlo que cerrar algo recién entregado por error.
            if (! $entregaLog || $entregaLog->created_at->gt(now()->subDays(self::DAYS_AFTER_ENTREGA))) {
                continue;
            }

            // rental_stage_logs.user_id es NOT NULL (no hay "usuario sistema"): se le atribuye al
            // asesor responsable del trato — es información real (es su trato), no un dato inventado.
            // Sin ningún responsable asignado (no debería pasar en un trato real) se deja intacto,
            // SIN cerrarlo — mejor no tocarlo que cerrarlo sin poder dejar el log/aviso correspondiente.
            $userId = $rental->broker_id ?? $rental->user_id;
            if (! $userId) {
                Log::warning("rentals:close-after-entrega — renta #{$rental->id} sin broker_id/user_id, se omite.");
                continue;
            }

            $rental->update([
                'stage' => 'cerrado',
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            RentalStageLog::create([
                'rental_process_id' => $rental->id,
                'user_id' => $userId,
                'from_stage' => 'entrega',
                'to_stage' => 'cerrado',
                'notes' => 'Cierre automático: ' . self::DAYS_AFTER_ENTREGA . ' días después de la entrega, sin administración de renta contratada.',
            ]);

            Notification::create([
                'user_id' => $userId,
                'type' => 'rental_auto_closed',
                'title' => 'Trato de renta cerrado automáticamente',
                'body' => 'La renta #' . $rental->id . ' se cerró sola (' . self::DAYS_AFTER_ENTREGA . ' días tras la entrega, sin administración contratada). Si sí administras esta renta, márcalo en la ficha del trato y repórtalo para reabrirlo.',
                'data' => ['url' => route('rentals.show', $rental->id), 'rental_id' => $rental->id],
            ]);

            $closed++;
        }

        Log::info("rentals:close-after-entrega — {$closed} trato(s) cerrado(s) automáticamente.");
        $this->info("{$closed} trato(s) cerrado(s).");

        return self::SUCCESS;
    }
}
