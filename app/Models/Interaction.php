<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Interaction extends Model
{
    protected $fillable = [
        'client_id', 'property_id', 'valuation_id', 'user_id', 'type', 'description',
        'scheduled_at', 'completed_at',
        'visit_token', 'confirmed_at', 'reminder_sent_at',
        'reschedule_requested_at', 'reschedule_message', 'send_confirmation_email',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at'             => 'datetime',
            'completed_at'             => 'datetime',
            'confirmed_at'             => 'datetime',
            'reminder_sent_at'         => 'datetime',
            'reschedule_requested_at'  => 'datetime',
            'send_confirmation_email'  => 'boolean',
        ];
    }

    public function isVisit(): bool
    {
        return $this->type === 'visit';
    }

    public function client() { return $this->belongsTo(Client::class); }
    public function property() { return $this->belongsTo(Property::class); }
    public function valuation() { return $this->belongsTo(PropertyValuation::class, 'valuation_id'); }
    public function user() { return $this->belongsTo(User::class); }
}
