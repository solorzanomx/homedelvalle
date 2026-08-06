<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImapProcessedMessage extends Model
{
    protected $fillable = ['message_id', 'type'];
}
