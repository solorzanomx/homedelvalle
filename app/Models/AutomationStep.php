<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutomationStep extends Model
{
    protected $fillable = ['automation_id', 'position', 'type', 'config'];

    protected function casts(): array
    {
        return ['config' => 'array'];
    }

    public function automation(): BelongsTo
    {
        return $this->belongsTo(Automation::class);
    }

    public function logs()
    {
        return $this->hasMany(AutomationStepLog::class, 'step_id');
    }

    // ── Config helpers ────────────────────────────────
    public function getDelayMinutes(): int
    {
        if ($this->type !== 'delay') return 0;
        $unit = $this->config['unit'] ?? 'hours';
        $value = (int) ($this->config['value'] ?? 1);
        return match($unit) {
            'minutes' => $value,
            'hours'   => $value * 60,
            'days'    => $value * 1440,
            default   => $value * 60,
        };
    }

    public function getLabel(): string
    {
        return match($this->type) {
            'delay'         => 'Esperar ' . ($this->config['value'] ?? '?') . ' ' . ($this->config['unit'] ?? 'horas'),
            'send_email'    => 'Email: ' . ($this->config['subject'] ?? 'Sin asunto'),
            'send_whatsapp' => 'WhatsApp: ' . Str::limit($this->config['message'] ?? '', 40),
            'condition'     => 'Si: ' . ($this->config['field'] ?? '?') . ' ' . ($this->config['operator'] ?? '?'),
            'create_task'   => 'Tarea: ' . ($this->config['title'] ?? ''),
            'move_pipeline' => 'Pipeline → ' . ($this->config['stage'] ?? '?'),
            'update_field'  => 'Campo: ' . ($this->config['field'] ?? '?'),
            'add_score'     => 'Score +' . ($this->config['points'] ?? 0),
            default         => $this->type,
        };
    }
}
