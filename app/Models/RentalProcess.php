<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RentalProcess extends Model
{
    protected $fillable = ['property_id', 'owner_client_id', 'tenant_client_id', 'broker_id', 'user_id', 'stage', 'monthly_rent', 'currency', 'deposit_amount', 'commission_amount', 'commission_percentage', 'guarantee_type', 'lease_start_date', 'lease_end_date', 'lease_duration_months', 'notes', 'status', 'completed_at', 'cancelled_at',];
    const STAGES = [
        'captacion' => 'Captacion',
        'verificacion' => 'Verificacion de Documentos',
        'publicacion' => 'Publicacion',
        'busqueda' => 'Busqueda de Arrendatario',
        'investigacion' => 'Investigacion / Poliza',
        'contrato' => 'Contrato',
        'entrega' => 'Entrega',
        'activo' => 'Activo',
        'renovacion' => 'Renovacion / Cierre',
        'cerrado' => 'Cerrado',
    ];

    const STAGE_COLORS = [
        'captacion' => '#8b5cf6',
        'verificacion' => '#f59e0b',
        'publicacion' => '#3b82f6',
        'busqueda' => '#06b6d4',
        'investigacion' => '#ec4899',
        'contrato' => '#f97316',
        'entrega' => '#10b981',
        'activo' => '#22c55e',
        'renovacion' => '#eab308',
        'cerrado' => '#94a3b8',
    ];

    const GUARANTEE_TYPES = [
        'deposito' => 'Deposito',
        'poliza_juridica' => 'Poliza Juridica',
        'fianza' => 'Fianza',
    ];

    protected function casts(): array
    {
        return [
            'monthly_rent' => 'decimal:2',
            'deposit_amount' => 'decimal:2',
            'commission_amount' => 'decimal:2',
            'commission_percentage' => 'decimal:2',
            'lease_start_date' => 'date',
            'lease_end_date' => 'date',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    // Relationships
    public function property() { return $this->belongsTo(Property::class); }
    public function ownerClient() { return $this->belongsTo(Client::class, 'owner_client_id'); }
    public function tenantClient() { return $this->belongsTo(Client::class, 'tenant_client_id'); }
    public function broker() { return $this->belongsTo(Broker::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function documents() { return $this->hasMany(Document::class); }
    public function stageLogs() { return $this->hasMany(RentalStageLog::class); }
    public function tasks() { return $this->hasMany(Task::class); }
    public function poliza() { return $this->hasOne(PolizaJuridica::class); }
    public function contracts() { return $this->hasMany(Contract::class); }

    // Scopes
    public function scopeActive($q) { return $q->where('status', 'active'); }
    public function scopeByStage($q, $stage) { return $q->where('stage', $stage); }
    public function scopeExpiringSoon($q, $days = 30)
    {
        return $q->where('status', 'active')
            ->where('stage', 'activo')
            ->whereBetween('lease_end_date', [now(), now()->addDays($days)]);
    }

    // Accessors
    public function getStageLabelAttribute(): string
    {
        return self::STAGES[$this->stage] ?? ucfirst($this->stage);
    }

    public function getStageColorAttribute(): string
    {
        return self::STAGE_COLORS[$this->stage] ?? '#94a3b8';
    }

    public function getDaysUntilExpirationAttribute(): ?int
    {
        if (!$this->lease_end_date) return null;
        return (int) now()->diffInDays($this->lease_end_date, false);
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->lease_end_date && $this->lease_end_date->isPast();
    }

    public function getGuaranteeTypeLabelAttribute(): string
    {
        return self::GUARANTEE_TYPES[$this->guarantee_type] ?? $this->guarantee_type;
    }
}
