<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['automation_rule_id', 'trigger_data', 'action_result', 'status', 'error_message'])]
class AutomationLog extends Model
{
    protected function casts(): array
    {
        return [
            'trigger_data' => 'array',
            'action_result' => 'array',
        ];
    }

    public function rule() { return $this->belongsTo(AutomationRule::class, 'automation_rule_id'); }
}
