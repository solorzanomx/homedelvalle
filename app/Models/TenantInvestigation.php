<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantInvestigation extends Model
{
    protected $fillable = [
        'rental_process_id', 'tenant_client_id', 'created_by',
        'occupation', 'employer', 'employment_years', 'income_type',
        'monthly_income', 'income_verified', 'credit_status', 'bureau_checked', 'credit_notes',
        'references_count', 'references_ok', 'references_notes',
        'asesor_recommendation', 'asesor_notes',
        'visible_to_owner', 'presented_at',
        'owner_decision', 'owner_decision_at', 'owner_decision_notes',
    ];

    const INCOME_TYPES = [
        'employed'       => 'Empleado (nómina)',
        'self_employed'  => 'Independiente / Freelance',
        'business_owner' => 'Dueño de negocio',
        'pension'        => 'Pensionado / Jubilado',
        'other'          => 'Otro',
    ];

    const CREDIT_STATUSES = [
        'excellent' => 'Excelente',
        'good'      => 'Bueno',
        'regular'   => 'Regular',
        'poor'      => 'Bajo',
    ];

    const CREDIT_COLORS = [
        'excellent' => '#10b981',
        'good'      => '#3b82f6',
        'regular'   => '#f59e0b',
        'poor'      => '#ef4444',
    ];

    const RECOMMENDATIONS = [
        'approve'     => 'Recomendar aprobación',
        'conditional' => 'Aprobación condicionada',
        'decline'     => 'No recomendar',
    ];

    const RECOMMENDATION_COLORS = [
        'approve'     => '#10b981',
        'conditional' => '#f59e0b',
        'decline'     => '#ef4444',
    ];

    const OWNER_DECISIONS = [
        'pending'   => 'Pendiente',
        'approved'  => 'Aprobado',
        'declined'  => 'Declinado',
        'more_info' => 'Solicita más información',
    ];

    protected function casts(): array
    {
        return [
            'monthly_income'     => 'decimal:2',
            'income_verified'    => 'boolean',
            'bureau_checked'     => 'boolean',
            'references_ok'      => 'boolean',
            'visible_to_owner'   => 'boolean',
            'presented_at'       => 'datetime',
            'owner_decision_at'  => 'datetime',
        ];
    }

    // Relationships
    public function rentalProcess() { return $this->belongsTo(RentalProcess::class); }
    public function tenantClient()  { return $this->belongsTo(Client::class, 'tenant_client_id'); }
    public function createdBy()     { return $this->belongsTo(User::class, 'created_by'); }

    // Accessors
    public function getIncomeTypeLabelAttribute(): string
    {
        return self::INCOME_TYPES[$this->income_type] ?? $this->income_type ?? '—';
    }

    public function getCreditStatusLabelAttribute(): string
    {
        return self::CREDIT_STATUSES[$this->credit_status] ?? '—';
    }

    public function getCreditStatusColorAttribute(): string
    {
        return self::CREDIT_COLORS[$this->credit_status] ?? '#94a3b8';
    }

    public function getRecommendationLabelAttribute(): string
    {
        return self::RECOMMENDATIONS[$this->asesor_recommendation] ?? '—';
    }

    public function getRecommendationColorAttribute(): string
    {
        return self::RECOMMENDATION_COLORS[$this->asesor_recommendation] ?? '#94a3b8';
    }

    public function getOwnerDecisionLabelAttribute(): string
    {
        return self::OWNER_DECISIONS[$this->owner_decision] ?? '—';
    }

    // Ratio renta/ingreso — retorna porcentaje
    public function getRentIncomeRatioAttribute(): ?float
    {
        $rent   = $this->rentalProcess?->monthly_rent;
        $income = $this->monthly_income;
        if (!$rent || !$income || $income <= 0) return null;
        return round(($rent / $income) * 100, 1);
    }

    // Semáforo del ratio
    public function getRentIncomeRatioColorAttribute(): string
    {
        $ratio = $this->rent_income_ratio;
        if ($ratio === null) return '#94a3b8';
        if ($ratio <= 35) return '#10b981';
        if ($ratio <= 50) return '#f59e0b';
        return '#ef4444';
    }

    public function getRentIncomeRatioLabelAttribute(): string
    {
        $ratio = $this->rent_income_ratio;
        if ($ratio === null) return '—';
        if ($ratio <= 35) return 'Excelente';
        if ($ratio <= 50) return 'Aceptable';
        return 'Alto';
    }
}
