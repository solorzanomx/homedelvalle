<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    protected $fillable = [
        'name', 'email', 'phone', 'whatsapp',
        'address', 'city', 'curp', 'rfc',
        'interest_types', 'lead_temperature', 'priority', 'initial_notes',
        'budget_min', 'budget_max', 'property_type',
        'broker_id', 'assigned_user_id',
        'photo', 'user_id',
        'marketing_channel_id', 'marketing_campaign_id',
        'acquisition_cost', 'utm_source', 'utm_medium', 'utm_campaign',
        'client_type', 'lead_source',
    ];

    protected $casts = [
        'budget_min' => 'decimal:2',
        'budget_max' => 'decimal:2',
        'acquisition_cost' => 'decimal:2',
        'interest_types' => 'array',
        'client_type' => 'string',
    ];

    public function broker(): BelongsTo
    {
        return $this->belongsTo(Broker::class);
    }

    public function portalUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function marketingChannel(): BelongsTo
    {
        return $this->belongsTo(MarketingChannel::class);
    }

    public function marketingCampaign(): BelongsTo
    {
        return $this->belongsTo(MarketingCampaign::class);
    }

    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class);
    }

    public function emails(): HasMany
    {
        return $this->hasMany(ClientEmail::class);
    }

    public function interactions(): HasMany
    {
        return $this->hasMany(Interaction::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function operations(): HasMany
    {
        return $this->hasMany(Operation::class);
    }

    public function ownedProperties(): HasMany
    {
        return $this->hasMany(Property::class, 'client_id');
    }

    // ── Marketing Automation ──────────────────────────
    public function segments(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Segment::class)->withPivot('entered_at', 'exited_at');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function leadEvents(): HasMany
    {
        return $this->hasMany(LeadEvent::class);
    }

    public function leadScore(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(LeadScore::class);
    }

    public function automationEnrollments(): HasMany
    {
        return $this->hasMany(AutomationEnrollment::class);
    }

    public function getScoreAttribute(): int
    {
        return $this->leadScore?->total_score ?? 0;
    }

    public function getGradeAttribute(): string
    {
        return $this->leadScore?->grade ?? 'D';
    }
}
