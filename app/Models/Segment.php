<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Segment extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'rules', 'is_active', 'is_system', 'cached_count', 'last_evaluated_at'];

    protected function casts(): array
    {
        return [
            'rules' => 'array',
            'is_active' => 'boolean',
            'is_system' => 'boolean',
            'last_evaluated_at' => 'datetime',
        ];
    }

    // ── Relations ─────────────────────────────────────
    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(Client::class)->withPivot('entered_at', 'exited_at');
    }

    public function automations()
    {
        return Automation::where('trigger_type', 'segment_enter')
            ->whereJsonContains('trigger_config->segment_id', $this->id)
            ->get();
    }

    // ── Scopes ────────────────────────────────────────
    public function scopeActive($q) { return $q->where('is_active', true); }

    // ── Rule evaluation operators ─────────────────────
    public const OPERATORS = [
        'equals', 'not_equals', 'contains', 'not_contains',
        'greater_than', 'less_than', 'is_empty', 'is_not_empty',
        'in', 'not_in', 'days_ago_more_than', 'days_ago_less_than',
    ];

    public const FIELDS = [
        'lead_temperature'  => 'Temperatura',
        'priority'          => 'Prioridad',
        'city'              => 'Ciudad',
        'property_type'     => 'Tipo de interes',
        'interest_types'    => 'Tipos de interes',
        'budget_min'        => 'Presupuesto minimo',
        'budget_max'        => 'Presupuesto maximo',
        'marketing_channel_id' => 'Canal de marketing',
        'created_at'        => 'Fecha de registro',
        'last_interaction'  => 'Ultima interaccion',
        'total_score'       => 'Score total',
        'grade'             => 'Grado del lead',
        'days_inactive'     => 'Dias sin actividad',
        'has_deal'          => 'Tiene deal',
        'has_operation'     => 'Tiene operacion',
        'emails_opened'     => 'Emails abiertos',
    ];
}
