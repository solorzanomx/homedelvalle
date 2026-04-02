<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['operation_id', 'user_id', 'from_stage', 'to_stage', 'from_phase', 'to_phase', 'notes'])]
class OperationStageLog extends Model
{
    public function operation() { return $this->belongsTo(Operation::class); }
    public function user() { return $this->belongsTo(User::class); }
}
