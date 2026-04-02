<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HelpOnboardingProgress extends Model
{
    protected $fillable = ['user_id', 'completed_steps', 'is_completed', 'completed_at'];

    protected function casts(): array
    {
        return ['completed_steps' => 'array', 'is_completed' => 'boolean', 'completed_at' => 'datetime'];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    public const STEPS = [
        'welcome'         => ['label' => 'Bienvenida', 'icon' => '👋'],
        'first_client'    => ['label' => 'Registra tu primer cliente', 'icon' => '👤'],
        'first_property'  => ['label' => 'Publica tu primera propiedad', 'icon' => '🏠'],
        'first_campaign'  => ['label' => 'Crea tu primera campaña', 'icon' => '📣'],
        'first_automation'=> ['label' => 'Activa una automatizacion', 'icon' => '⚡'],
        'first_operation' => ['label' => 'Inicia una operacion', 'icon' => '📋'],
    ];

    public function completeStep(string $step): void
    {
        $steps = $this->completed_steps ?? [];
        if (!in_array($step, $steps)) {
            $steps[] = $step;
            $this->completed_steps = $steps;
            if (count($steps) >= count(self::STEPS)) {
                $this->is_completed = true;
                $this->completed_at = now();
            }
            $this->save();
        }
    }

    public function isStepCompleted(string $step): bool
    {
        return in_array($step, $this->completed_steps ?? []);
    }

    public function getProgressPercent(): int
    {
        return (int) round((count($this->completed_steps ?? []) / count(self::STEPS)) * 100);
    }
}
