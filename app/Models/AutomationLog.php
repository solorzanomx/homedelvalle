<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AutomationLog extends Model
{
    protected $fillable = ['automation_rule_id', 'trigger_data', 'action_result', 'status', 'error_message'];
    protected function casts(): array
    {
        return [
            'trigger_data' => 'array',
            'action_result' => 'array',
        ];
    }

    public function rule() { return $this->belongsTo(AutomationRule::class, 'automation_rule_id'); }
}
