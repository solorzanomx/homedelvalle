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

    /**
     * Lo que capturó el cliente vs lo que la IA leyó en el documento, para
     * decidir sin cambiar de pantalla. Vacío si el documento no se leyó.
     *
     * @return array<int, array{label:string, captured:?string, extracted:?string, ok:?bool}>
     */
    public static function comparison(Document $d): array
    {
        $ai = $d->ai_extracted_data;
        $c = $d->client;
        if (! is_array($ai) || empty($ai['legible']) || ! $c) {
            return [];
        }

        $rows = [];
        $add = function (string $label, ?string $captured, ?string $extracted, ?bool $ok) use (&$rows) {
            if ($captured || $extracted) {
                $rows[] = ['label' => $label, 'captured' => $captured ?: null, 'extracted' => $extracted ?: null, 'ok' => $ok];
            }
        };

        if (DocumentUploadGuide::kind($d->category) === DocumentUploadGuide::KIND_ID) {
            $add('Nombre', $c->name, $ai['nombre_completo'] ?? null, self::similar($c->name, $ai['nombre_completo'] ?? null));
            $add('CURP', $c->curp, $ai['curp'] ?? null, ($c->curp && ! empty($ai['curp'])) ? strtoupper(trim($c->curp)) === strtoupper(trim($ai['curp'])) : null);
            $vig = ! empty($ai['vigencia_mes']) ? sprintf('%02d/%s', $ai['vigencia_mes'], $ai['vigencia_anio'] ?? '') : null;
            $mine = $c->id_expiry_month ? sprintf('%02d/%s', $c->id_expiry_month, $c->id_expiry_year) : null;
            $add('Vigencia', $mine, $vig, ($mine && $vig) ? $mine === $vig : null);
        } elseif (isset($ai['calle_numero']) || isset($ai['codigo_postal'])) {
            $mine = trim(implode(', ', array_filter([$c->address_street, $c->address_colony, $c->address_zip])));
            $doc = trim(implode(', ', array_filter([$ai['calle_numero'] ?? null, $ai['colonia'] ?? null, $ai['codigo_postal'] ?? null])));
            $zipOk = ($c->address_zip && ! empty($ai['codigo_postal'])) ? $c->address_zip === $ai['codigo_postal'] : null;
            $add('Domicilio', $mine, $doc, $zipOk);
            $add('Fecha del recibo', null, $ai['fecha_recibo'] ?? null, null);
        } elseif (isset($ai['titular'])) {
            $add('Titular', $c->name, $ai['titular'] ?? null, self::similar($c->name, $ai['titular'] ?? null));
            $add('Institución / empresa', null, $ai['institucion'] ?? null, null);
            $add('Ingreso neto (nómina)', $c->income_amount ? number_format((float) $c->income_amount, 2) : null, $ai['ingreso_neto'] ?? null, null);
            $per = trim(($ai['periodo_inicio'] ?? '') . ' → ' . ($ai['periodo_fin'] ?? ''), ' →');
            $add('Periodo', null, $per ?: null, null);
        }

        return $rows;
    }

    /** Historial del documento listo para el visor: [['d/m H:i', 'texto'], ...]. */
    public static function history(Document $d): array
    {
        return $d->events->map(function ($e) {
            $who = $e->user?->name;
            $text = (\App\Models\DocumentEvent::LABELS[$e->type] ?? $e->type)
                . ($e->note ? ': ' . $e->note : '')
                . ($who && in_array($e->type, ['verified', 'rejected'], true) ? " ({$who})" : '');
            return [$e->created_at->format('d/m H:i'), $text];
        })->values()->all();
    }

    /** ¿Los dos nombres se parecen lo bastante (tokens, sin acentos)? null si falta alguno. */
    public static function similar(?string $a, ?string $b): ?bool
    {
        $norm = fn($s) => array_filter(explode(' ', preg_replace('/[^a-z ]/', '', strtolower(\Illuminate\Support\Str::ascii((string) $s)))));
        $x = $norm($a);
        $y = $norm($b);
        if (! $x || ! $y) {
            return null;
        }

        return count(array_intersect($x, $y)) / min(count($x), count($y)) >= 0.6;
    }
}
