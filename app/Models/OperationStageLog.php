<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperationStageLog extends Model
{
    protected $fillable = ['operation_id', 'user_id', 'from_stage', 'to_stage', 'from_phase', 'to_phase', 'notes'];
    public function operation() { return $this->belongsTo(Operation::class); }
    public function user() { return $this->belongsTo(User::class); }
}
