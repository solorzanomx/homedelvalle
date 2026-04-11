<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadScore extends Model
{
    protected $fillable = [
        'client_id', 'total_score', 'engagement_score',
        'activity_score', 'profile_score', 'grade', 'last_activity_at',
    ];

    protected function casts(): array
    {
        return ['last_activity_at' => 'datetime'];
    }

    public function client(): BelongsTo { return $this->belongsTo(Client::class); }

    // ── Grade thresholds ──────────────────────────────
    public const GRADES = [
        'A' => ['min' => 80, 'label' => 'Listo para cerrar',  'color' => '#16a34a'],
        'B' => ['min' => 50, 'label' => 'Interesado activo',  'color' => '#2563eb'],
        'C' => ['min' => 20, 'label' => 'Tibio',              'color' => '#f59e0b'],
        'D' => ['min' => 0,  'label' => 'Frio / Nuevo',       'color' => '#6b7280'],
    ];

    public function recalculateGrade(): void
    {
        $total = $this->total_score;
        $grade = 'D';
        foreach (['A', 'B', 'C'] as $g) {
            if ($total >= self::GRADES[$g]['min']) { $grade = $g; break; }
        }
        if ($this->grade !== $grade) {
            $this->update(['grade' => $grade]);
        }
    }

    public function addPoints(int $engagement = 0, int $activity = 0, int $profile = 0): void
    {
        $this->increment('engagement_score', $engagement);
        $this->increment('activity_score', $activity);
        $this->increment('profile_score', $profile);
        $this->update([
            'total_score' => $this->engagement_score + $this->activity_score + $this->profile_score,
            'last_activity_at' => now(),
        ]);
        $this->refresh();
        $this->recalculateGrade();

        // Check if score crossed a threshold for automation triggers
        $client = $this->client ?? Client::find($this->client_id);
        if ($client) {
            app(\App\Services\AutomationEngine::class)->processScoreThreshold($client, $this->total_score);
        }
    }

    public static function getOrCreate(int $clientId): static
    {
        return static::firstOrCreate(['client_id' => $clientId]);
    }
}
