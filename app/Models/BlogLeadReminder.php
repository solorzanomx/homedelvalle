<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlogLeadReminder extends Model
{
    public $timestamps = false;
    protected $fillable = ['form_submission_id', 'tier', 'created_at'];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }
}
