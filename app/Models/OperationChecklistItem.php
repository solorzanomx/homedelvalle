<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['operation_id', 'stage_checklist_template_id', 'stage', 'is_completed', 'completed_by', 'completed_at', 'notes'])]
class OperationChecklistItem extends Model
{
    protected function casts(): array
    {
        return [
            'is_completed' => 'boolean',
            'completed_at' => 'datetime',
        ];
    }

    public function operation() { return $this->belongsTo(Operation::class); }
    public function template() { return $this->belongsTo(StageChecklistTemplate::class, 'stage_checklist_template_id'); }
    public function completedByUser() { return $this->belongsTo(User::class, 'completed_by'); }
}
