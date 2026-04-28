<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailTemplateTesting extends Model
{
    protected $table = 'email_template_testing';

    protected $fillable = [
        'template_id',
        'test_email',
        'test_data',
        'status',
        'error_message',
        'sent_at',
    ];

    protected $casts = [
        'test_data' => 'array',
        'sent_at' => 'datetime',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(CustomEmailTemplate::class, 'template_id');
    }

    public function wasSent(): bool
    {
        return $this->status === 'sent';
    }

    public function hasFailed(): bool
    {
        return $this->status === 'failed';
    }
}
