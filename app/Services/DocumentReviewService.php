<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentEvent;
use App\Models\Notification;
use App\Support\DocumentReviewInbox;
use App\Support\RentalExpedienteStatus;
use Illuminate\Support\Facades\Auth;

/**
 * Único lugar donde se aprueba / rechaza un documento (visor, bandeja, formularios
 * viejos y aprobación en bloque pasan por aquí) para que TODAS las consecuencias
 * ocurran siempre: reinicio de avisos, flujo de Captación, historial y el aviso
 * de "expediente completo".
 */
class DocumentReviewService
{
    public function apply(Document $doc, string $status, ?string $reason = null, ?int $userId = null): Document
    {
        $userId ??= Auth::id();
        $data = ['status' => $status];
        $resetNotice = ['rejected_at' => null, 'rejection_notified_at' => null, 'rejection_notified_via' => null, 'rejection_reminders_sent' => 0, 'rejection_reminded_at' => null];

        if ($status === 'verified') {
            $data += ['verified_at' => now(), 'verified_by' => $userId, 'rejection_reason' => null] + $resetNotice;
        } elseif ($status === 'rejected') {
            // Reinicia el aviso: el scheduler juntará todos los rechazos del cliente en un solo correo.
            $data += ['rejection_reason' => $reason] + $resetNotice + ['rejected_at' => now()];
        }

        $doc->update($data);

        // Documentos de una Captación tienen flujo propio (captacion_status + recalcular etapa).
        if ($doc->captacion_id && $doc->captacion_status !== null) {
            $captaciones = app(CaptacionService::class);
            if ($status === 'verified') {
                $captaciones->approveDocument($doc);
            } elseif ($status === 'rejected') {
                $captaciones->rejectDocument($doc, $reason);
            }
        }

        if (in_array($status, ['verified', 'rejected'], true)) {
            DocumentEvent::log($doc, $status, $status === 'rejected' ? $reason : null, $userId);
        }
        if ($status === 'verified') {
            $this->notifyIfExpedienteComplete($doc);
        }

        DocumentReviewInbox::forgetCount();

        return $doc;
    }

    /**
     * Aprobación en bloque. Se salta lo que NO debe aprobarse a ciegas: el
     * comprobante de apartado (confirmar apartado genera el recibo) y lo que ya
     * no está por revisar.
     *
     * @param  int[]  $ids
     * @return array{approved:int, skipped:array<int,string>}
     */
    public function bulkApprove(array $ids): array
    {
        $approved = 0;
        $skipped = [];

        foreach (Document::with('rentalProcess')->whereIn('id', $ids)->get() as $doc) {
            $reviewable = $doc->status === 'received' || ($doc->captacion_id && $doc->captacion_status === 'pendiente');
            if (! $reviewable) {
                $skipped[$doc->id] = 'ya no estaba por revisar';
                continue;
            }
            if ($doc->category === 'comprobante_apartado' && $doc->rentalProcess && ! $doc->rentalProcess->apartado_paid_at) {
                $skipped[$doc->id] = 'el comprobante de apartado se confirma desde la renta';
                continue;
            }
            $this->apply($doc, 'verified');
            $approved++;
        }

        return ['approved' => $approved, 'skipped' => $skipped];
    }

    /** Avisa al asesor (una sola vez por renta) cuando el expediente del inquilino queda completo y aprobado. */
    private function notifyIfExpedienteComplete(Document $doc): void
    {
        $rental = $doc->rentalProcess;
        if (! $rental || ! RentalExpedienteStatus::isFullyComplete($rental)) {
            return;
        }

        $already = Notification::where('type', 'expediente_completo')->where('data->rental_id', $rental->id)->exists();
        $userId = $rental->broker_id ?? $rental->user_id;
        if ($already || ! $userId) {
            return;
        }

        $name = $rental->tenantClient?->name ?? 'El inquilino';
        Notification::create([
            'user_id' => $userId,
            'type' => 'expediente_completo',
            'title' => 'Expediente completo y aprobado',
            'body' => "El expediente de {$name} en la renta #{$rental->id} tiene todos sus documentos aprobados" . ($rental->obligado_client_id && $rental->obligado_required !== false ? ' (incluido su obligado solidario)' : '') . '. Ya puedes avanzar a la siguiente etapa.',
            'data' => ['url' => route('rentals.show', $rental->id), 'rental_id' => $rental->id],
        ]);
    }
}
