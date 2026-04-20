<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AutomationEnrollment extends Model
{
    protected $fillable = [
        'automation_id', 'client_id', 'current_step',
        'status', 'next_run_at', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'next_run_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function automation(): BelongsTo { return $this->belongsTo(Automation::class); }
    public function client(): BelongsTo    { return $this->belongsTo(Client::class); }

    public function stepLogs(): HasMany
    {
        return $this->hasMany(AutomationStepLog::class, 'enrollment_id');
    }

    // ── Scopes ────────────────────────────────────────
    public function scopeActive($q)  { return $q->where('status', 'active'); }
    public function scopeReady($q)   { return $q->active()->where('next_run_at', '<=', now()); }

    // ── Helpers ───────────────────────────────────────
    public function getCurrentStep(): ?AutomationStep
    {
        return $this->automation->steps()->where('position', $this->current_step)->first();
    }

    public function getNextStep(): ?AutomationStep
    {
        return $this->automation->steps()->where('position', '>', $this->current_step)->orderBy('position')->first();
    }

    public function markCompleted(): void
    {
        try {
            $this->update(['status' => 'completed', 'completed_at' => now(), 'next_run_at' => null]);
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            // Duplicate completed enrollment exists — just delete this one
            $this->delete();
        }
    }

    public function advance(): void
    {
        $next = $this->getNextStep();
        if (!$next) {
            $this->markCompleted();
            return;
        }
        $this->update(['current_step' => $next->position, 'next_run_at' => now()]);
    }
}
