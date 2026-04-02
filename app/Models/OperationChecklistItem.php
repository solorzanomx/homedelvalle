<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperationChecklistItem extends Model
{
    protected $fillable = ['operation_id', 'stage_checklist_template_id', 'stage', 'is_completed', 'completed_by', 'completed_at', 'notes'];
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
