<?php

namespace App\Console\Commands;

use App\Models\Contract;
use App\Models\Notification;
use Illuminate\Console\Command;

class CheckContractPendingSignature extends Command
{
    protected $signature = 'contracts:check-pending-signature';

    protected $description = 'Recuerda al broker dar seguimiento cuando la versión vigente de un contrato lleva días sin firmarse ni tener una versión nueva.';

    private const DAYS_THRESHOLD = 5;
    private const ALERT_TYPE = 'contract_version_pending_signature';

    public function handle(): int
    {
        $stale = Contract::with(['currentVersion', 'operation.user', 'rentalProcess.user'])
            ->whereHas('currentVersion', function ($q) {
                $q->where('signature_status', '!=', 'signed')
                    ->where('created_at', '<', now()->subDays(self::DAYS_THRESHOLD));
            })
            ->where(function ($q) {
                $q->whereHas('operation', fn ($q2) => $q2->where('status', 'active'))
                    ->orWhereHas('rentalProcess', fn ($q2) => $q2->where('status', 'active'));
            })
            ->get();

        $notified = 0;

        foreach ($stale as $contract) {
            $userId = $contract->operation?->user_id ?? $contract->rentalProcess?->user_id;
            if (!$userId) {
                continue;
            }

            $alreadyNotifiedToday = Notification::where('type', self::ALERT_TYPE)
                ->where('data->contract_id', $contract->id)
                ->where('created_at', '>=', now()->startOfDay())
                ->exists();

            if ($alreadyNotifiedToday) {
                continue;
            }

            $days = (int) $contract->currentVersion->created_at->diffInDays(now());
            $url = $contract->operation_id
                ? route('operations.show', $contract->operation_id)
                : route('rentals.show', $contract->rental_process_id);

            Notification::create([
                'user_id' => $userId,
                'type' => self::ALERT_TYPE,
                'title' => 'Contrato pendiente de firma',
                'body' => "\"{$contract->title}\" (versión {$contract->currentVersion->version_number}) lleva {$days} días sin firmarse ni tener una nueva versión — dale seguimiento.",
                'data' => ['url' => $url, 'contract_id' => $contract->id],
            ]);

            $notified++;
        }

        $this->info("Done. {$notified} recordatorios de contrato pendiente enviados.");

        return Command::SUCCESS;
    }
}
