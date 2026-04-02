<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class LeadEvent extends Model
{
    protected $fillable = [
        'client_id', 'event', 'source', 'eventable_type', 'eventable_id',
        'properties', 'score_delta', 'occurred_at',
    ];

    protected function casts(): array
    {
        return ['properties' => 'array', 'occurred_at' => 'datetime'];
    }

    public function client(): BelongsTo { return $this->belongsTo(Client::class); }
    public function eventable(): MorphTo { return $this->morphTo(); }

    // ── Events list ───────────────────────────────────
    public const EVENTS = [
        'message_sent'     => 'Mensaje enviado',
        'message_opened'   => 'Mensaje abierto',
        'message_replied'  => 'Mensaje respondido',
        'call_completed'   => 'Llamada completada',
        'visit_scheduled'  => 'Visita agendada',
        'visit_completed'  => 'Visita completada',
        'form_submitted'   => 'Formulario enviado',
        'score_changed'    => 'Score actualizado',
        'segment_entered'  => 'Entro a segmento',
        'segment_exited'   => 'Salio de segmento',
        'pipeline_entered' => 'Entro a pipeline',
        'stage_changed'    => 'Cambio de etapa',
        'task_completed'   => 'Tarea completada',
        'deal_created'     => 'Deal creado',
    ];

    // ── Factory ───────────────────────────────────────
    public static function record(int $clientId, string $event, array $attrs = []): static
    {
        return static::create(array_merge([
            'client_id'   => $clientId,
            'event'       => $event,
            'occurred_at' => now(),
        ], $attrs));
    }
}
