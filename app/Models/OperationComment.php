<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperationComment extends Model
{
    protected $fillable = ['operation_id', 'user_id', 'body'];
    public function operation() { return $this->belongsTo(Operation::class); }
    public function user() { return $this->belongsTo(User::class); }
}
