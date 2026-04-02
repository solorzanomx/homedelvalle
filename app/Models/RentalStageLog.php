<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RentalStageLog extends Model
{
    protected $fillable = ['rental_process_id', 'user_id', 'from_stage', 'to_stage', 'notes'];
    public function rentalProcess() { return $this->belongsTo(RentalProcess::class); }
    public function user() { return $this->belongsTo(User::class); }
}
