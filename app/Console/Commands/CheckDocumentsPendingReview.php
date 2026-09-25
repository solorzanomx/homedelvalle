<?php

namespace App\Console\Commands;

use App\Models\Notification;
use App\Models\User;
use App\Support\DocumentReviewInbox;
use Illuminate\Console\Command;

class CheckDocumentsPendingReview extends Command
{
    protected $signature = 'documents:check-pending-review';

    protected $description = 'Avisa al asesor cuando hay documentos subidos por clientes con más de 24 h sin revisar (bandeja Docs por revisar)';

    private const ALERT_TYPE = 'documentos_sin_revisar';

    /**
     * UNA notificación por asesor con el resumen (no una por documento: 15
     * documentos = 1 aviso, no 15). Mismo patrón que CheckValuacionPendiente:
     * no repite el mismo día. Si un documento no tiene asesor identificable, el
     * aviso va a los administradores para que nada quede sin dueño.
     */
    public function handle(): int
    {
        $late = DocumentReviewInbox::query()
            ->where('created_at', '<', now()->subHours(DocumentReviewInbox::LATE_HOURS))
            ->with(['client', 'rentalProcess', 'operation'])
            ->get();

        if ($late->isEmpty()) {
            $this->info('Sin documentos atrasados.');
            return self::SUCCESS;
        }

        $admins = null;
        $byUser = [];
        foreach ($late as $doc) {
            $uid = DocumentReviewInbox::ownerUserId($doc);
            if (! $uid) {
                $admins ??= User::where('role', 'admin')->where('is_active', true)->pluck('id')->all();
                foreach ($admins as $adminId) {
                    $byUser[$adminId][] = $doc;
                }
                continue;
            }
            $byUser[$uid][] = $doc;
        }

        $notified = 0;
        foreach ($byUser as $userId => $docs) {
            $already = Notification::where('type', self::ALERT_TYPE)
                ->where('user_id', $userId)
                ->where('created_at', '>=', now()->startOfDay())
                ->exists();
            if ($already) {
                continue;
            }

            $clients = collect($docs)->map(fn($d) => $d->client?->name)->filter()->unique()->values();
            $oldestHours = (int) collect($docs)->max(fn($d) => $d->created_at->diffInHours(now()));

            Notification::create([
                'user_id' => $userId,
                'type' => self::ALERT_TYPE,
                'title' => 'Documentos sin revisar',
                'body' => count($docs) . ' documento(s) llevan más de ' . DocumentReviewInbox::LATE_HOURS . ' h esperando tu revisión'
                    . ($clients->isNotEmpty() ? ' (' . $clients->take(3)->implode(', ') . ($clients->count() > 3 ? ' y más' : '') . ')' : '')
                    . '. El más antiguo lleva ' . $oldestHours . ' h. Un cliente que subió sus papeles y no recibe respuesta se enfría.',
                'data' => ['url' => route('documents.inbox', ['scope' => 'late'])],
            ]);
            $notified++;
        }

        $this->info("{$late->count()} documento(s) atrasados; {$notified} asesor(es) notificados.");
        return self::SUCCESS;
    }
}
