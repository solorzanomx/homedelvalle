<?php

namespace App\Support;

use App\Models\Document;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

/**
 * Bandeja central "Documentos por revisar" (2026-09-25).
 *
 * Un documento está por revisar si lo subió un CLIENTE desde el Portal y aún
 * no lo aprueba/rechaza un asesor: status='received' (rentas, expediente,
 * documentos generales) o captacion_status='pendiente' (documentos de
 * Captación, que tienen su propio flujo). Los PDFs que genera el broker
 * (presentación, opinión de valor, recibos, contratos) no se revisan.
 */
class DocumentReviewInbox
{
    /** Documentos que genera el sistema/broker — no pasan por revisión. */
    const GENERATED = [
        'presentation_pdf', 'opinion_valor', 'propuesta_servicios', 'oferta_compra',
        'recibo_apartado', 'recibo_investigacion', 'contrato_exclusiva', 'contrato_exclusiva_renta',
        'contrato_compraventa', 'adendum_comision',
    ];

    /** Horas sin revisar para considerarlo atrasado. */
    const LATE_HOURS = 24;

    public static function query(): Builder
    {
        return Document::query()
            ->whereNotIn('category', self::GENERATED)
            ->whereHas('uploader', fn($q) => $q->where('role', 'client'))
            ->where(function ($q) {
                $q->where('status', 'received')
                  ->orWhere(fn($q2) => $q2->whereNotNull('captacion_id')->where('captacion_status', 'pendiente'));
            });
    }

    /** Total para el contador del menú (caché corta: se renderiza en cada página). */
    public static function count(): int
    {
        return (int) Cache::remember('docs:inbox:count', 30, fn() => self::query()->count());
    }

    public static function forgetCount(): void
    {
        Cache::forget('docs:inbox:count');
    }

    public static function isLate(Document $doc): bool
    {
        return $doc->created_at && $doc->created_at->lt(now()->subHours(self::LATE_HOURS));
    }

    /** Dónde vive el documento: etiqueta, tipo y enlace al lugar de trabajo. */
    public static function context(Document $d): array
    {
        if ($d->rental_process_id) {
            return ['kind' => 'renta', 'label' => 'Renta #' . $d->rental_process_id, 'url' => route('rentals.show', $d->rental_process_id)];
        }
        if ($d->operation_id) {
            return ['kind' => 'venta', 'label' => 'Operación #' . $d->operation_id, 'url' => route('operations.show', $d->operation_id)];
        }
        if ($d->captacion_id) {
            return ['kind' => 'captacion', 'label' => 'Captación #' . $d->captacion_id, 'url' => route('admin.captaciones.show', $d->captacion_id)];
        }
        return ['kind' => 'expediente', 'label' => 'Expediente del cliente', 'url' => $d->client_id ? route('clients.show', $d->client_id) : null];
    }

    /** A quién avisar: asesor del cliente, o el de la renta/operación. */
    public static function ownerUserId(Document $d): ?int
    {
        return $d->client?->assigned_user_id
            ?? $d->rentalProcess?->broker_id
            ?? $d->rentalProcess?->user_id
            ?? $d->operation?->broker_id
            ?? $d->operation?->user_id;
    }
}
