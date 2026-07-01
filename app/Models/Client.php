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
        // Datos legales — nombre desglosado
        'first_name', 'last_name_paterno', 'last_name_materno',
        // Datos personales
        'birth_date', 'birth_state', 'gender', 'nationality', 'marital_status', 'occupation',
        // Identificación oficial
        'id_type', 'id_number', 'id_expiry',
        // Domicilio estructurado
        'address_street', 'address_colony', 'address_municipality', 'address_state', 'address_zip',
        // Datos para contratos de renta
        'marital_regime', 'spouse_name', 'spouse_curp', 'bank_clabe', 'bank_name',
    ];

    protected $casts = [
        'budget_min'   => 'decimal:2',
        'budget_max'   => 'decimal:2',
        'acquisition_cost' => 'decimal:2',
        'interest_types'   => 'array',
        'client_type'  => 'string',
        'birth_date'   => 'date',
        'id_expiry'    => 'date',
        'curp_verified_at' => 'datetime',
        'rfc_verified_at'  => 'datetime',
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

    // ── Accessors de nombre legal ─────────────────────────────

    /** Nombre completo para contratos: APELLIDO PATERNO APELLIDO MATERNO NOMBRE(S) */
    public function getFullNameLegalAttribute(): string
    {
        if ($this->first_name && $this->last_name_paterno) {
            return strtoupper(trim(
                ($this->last_name_paterno ?? '') . ' ' .
                ($this->last_name_materno ?? '') . ' ' .
                ($this->first_name ?? '')
            ));
        }
        return strtoupper($this->name ?? '');
    }

    /** Nombre completo natural: Nombre(s) Apellido Paterno Apellido Materno */
    public function getFullNameAttribute(): string
    {
        if ($this->first_name && $this->last_name_paterno) {
            return trim(
                ($this->first_name ?? '') . ' ' .
                ($this->last_name_paterno ?? '') . ' ' .
                ($this->last_name_materno ?? '')
            );
        }
        return $this->name ?? '';
    }

    /** Calcula % de completitud de ficha legal (0-100) */
    public function getLegalCompletenessAttribute(): int
    {
        $fields = [
            'first_name', 'last_name_paterno', 'last_name_materno',
            'birth_date', 'birth_state', 'gender', 'nationality', 'marital_status',
            'curp', 'rfc',
            'id_type', 'id_number',
            'address_street', 'address_colony', 'address_municipality', 'address_state', 'address_zip',
        ];
        $filled = collect($fields)->filter(fn($f) => !empty($this->$f))->count();
        return (int) round(($filled / count($fields)) * 100);
    }

    /** Domicilio estructurado como string para contratos */
    public function getLegalAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address_street,
            $this->address_colony ? 'Col. ' . $this->address_colony : null,
            $this->address_municipality,
            $this->address_state,
            $this->address_zip ? 'C.P. ' . $this->address_zip : null,
        ]);
        return implode(', ', $parts) ?: ($this->address ?? '');
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
