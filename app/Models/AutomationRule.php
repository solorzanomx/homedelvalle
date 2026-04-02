<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AutomationRule extends Model
{
    protected $fillable = ['name', 'trigger', 'conditions', 'action', 'action_config', 'is_active', 'last_triggered_at', 'trigger_count'];
    protected function casts(): array
    {
        return [
            'conditions' => 'array',
            'action_config' => 'array',
            'is_active' => 'boolean',
            'last_triggered_at' => 'datetime',
            'trigger_count' => 'integer',
        ];
    }

    public function logs() { return $this->hasMany(AutomationLog::class); }

    public function scopeActive($q) { return $q->where('is_active', true); }
    public function scopeForTrigger($q, $trigger) { return $q->where('trigger', $trigger); }
}
