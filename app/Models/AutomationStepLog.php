<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutomationStepLog extends Model
{
    protected $fillable = ['enrollment_id', 'step_id', 'status', 'result', 'error', 'executed_at'];

    protected function casts(): array
    {
        return ['result' => 'array', 'executed_at' => 'datetime'];
    }

    public function enrollment(): BelongsTo { return $this->belongsTo(AutomationEnrollment::class, 'enrollment_id'); }
    public function step(): BelongsTo       { return $this->belongsTo(AutomationStep::class, 'step_id'); }
}
