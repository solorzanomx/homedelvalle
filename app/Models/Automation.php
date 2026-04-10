<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Automation extends Model
{
    protected $fillable = [
        'name', 'description', 'trigger_type', 'trigger_config',
        'is_active', 'allow_reentry', 'enrollment_count', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'trigger_config' => 'array',
            'is_active' => 'boolean',
            'allow_reentry' => 'boolean',
        ];
    }

    // ── Trigger types ─────────────────────────────────
    public const TRIGGERS = [
        'form_submitted'  => 'Formulario recibido',
        'segment_enter'   => 'Entra a segmento',
        'segment_exit'    => 'Sale de segmento',
        'stage_change'    => 'Cambio de etapa',
        'new_client'      => 'Nuevo cliente',
        'manual'          => 'Inscripcion manual',
        'score_threshold' => 'Score alcanzado',
        'inactivity'      => 'Dias sin actividad',
    ];

    // ── Step types ────────────────────────────────────
    public const STEP_TYPES = [
        'delay'          => 'Esperar',
        'send_email'     => 'Enviar email',
        'send_whatsapp'  => 'Enviar WhatsApp',
        'condition'       => 'Condicion',
        'create_task'    => 'Crear tarea',
        'move_pipeline'  => 'Mover a pipeline',
        'update_field'   => 'Actualizar campo',
        'add_score'      => 'Sumar puntos',
    ];

    // ── Relations ─────────────────────────────────────
    public function steps(): HasMany
    {
        return $this->hasMany(AutomationStep::class)->orderBy('position');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(AutomationEnrollment::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Scopes ────────────────────────────────────────
    public function scopeActive($q) { return $q->where('is_active', true); }

    // ── Helpers ───────────────────────────────────────
    public function activeEnrollments(): HasMany
    {
        return $this->enrollments()->where('status', 'active');
    }

    public function getFirstStep(): ?AutomationStep
    {
        return $this->steps()->orderBy('position')->first();
    }
}
