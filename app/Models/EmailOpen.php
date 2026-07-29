<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class EmailOpen extends Model
{
    protected $fillable = [
        'token', 'custom_email_template_id', 'recipient_email',
        'trackable_type', 'trackable_id', 'opened_at', 'opens_count', 'user_agent',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
    ];

    public function trackable(): MorphTo
    {
        return $this->morphTo();
    }

    public function template()
    {
        return $this->belongsTo(CustomEmailTemplate::class, 'custom_email_template_id');
    }
}
