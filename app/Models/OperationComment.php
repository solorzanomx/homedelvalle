<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['operation_id', 'user_id', 'body'])]
class OperationComment extends Model
{
    public function operation() { return $this->belongsTo(Operation::class); }
    public function user() { return $this->belongsTo(User::class); }
}
