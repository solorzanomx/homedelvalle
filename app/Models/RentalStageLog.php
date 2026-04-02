<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['rental_process_id', 'user_id', 'from_stage', 'to_stage', 'notes'])]
class RentalStageLog extends Model
{
    public function rentalProcess() { return $this->belongsTo(RentalProcess::class); }
    public function user() { return $this->belongsTo(User::class); }
}
