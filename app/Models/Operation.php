<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'type', 'target_type', 'phase', 'stage', 'status',
    'property_id', 'client_id', 'secondary_client_id', 'broker_id', 'user_id',
    'source_operation_id',
    'amount', 'monthly_rent', 'currency', 'deposit_amount',
    'commission_amount', 'commission_percentage', 'guarantee_type',
    'expected_close_date', 'lease_start_date', 'lease_end_date', 'lease_duration_months',
    'notes', 'closed_at', 'completed_at', 'cancelled_at',
])]
class Operation extends Model
{
    const STAGES = [
        'lead' => 'Lead',
        'contacto' => 'Contacto',
        'visita' => 'Visita',
        'revision_docs' => 'Revision Docs',
        'avaluo' => 'Avaluo',
        'mejoras' => 'Mejoras',
        'exclusiva' => 'Exclusiva',
        'fotos_video' => 'Fotos/Video',
        'carpeta_lista' => 'Carpeta Lista',
        'publicacion' => 'Publicacion',
        'busqueda' => 'Busqueda',
        'investigacion' => 'Investigacion',
        'contrato' => 'Contrato',
        'entrega' => 'Entrega',
        'cierre' => 'Cierre',
        'activo' => 'Activo',
        'renovacion' => 'Renovacion',
    ];

    const VENTA_STAGES = ['lead','contacto','visita','exclusiva','publicacion','busqueda','investigacion','contrato','entrega','cierre'];
    const RENTA_STAGES = ['lead','contacto','visita','exclusiva','publicacion','busqueda','investigacion','contrato','entrega','cierre','activo','renovacion'];
    const CAPTACION_STAGES = ['lead','contacto','visita','revision_docs','avaluo','mejoras','exclusiva','fotos_video','carpeta_lista'];

    const PHASE_MAP = [
        'lead' => 'captacion', 'contacto' => 'captacion', 'visita' => 'captacion', 'exclusiva' => 'captacion',
        'revision_docs' => 'captacion', 'avaluo' => 'captacion', 'mejoras' => 'captacion',
        'fotos_video' => 'captacion', 'carpeta_lista' => 'captacion',
        'publicacion' => 'operacion', 'busqueda' => 'operacion', 'investigacion' => 'operacion',
        'contrato' => 'operacion', 'entrega' => 'operacion', 'cierre' => 'operacion',
        'activo' => 'operacion', 'renovacion' => 'operacion',
    ];

    const STAGE_COLORS = [
        'lead' => '#94a3b8', 'contacto' => '#60a5fa', 'visita' => '#818cf8', 'exclusiva' => '#a78bfa',
        'revision_docs' => '#f59e0b', 'avaluo' => '#ef4444', 'mejoras' => '#14b8a6',
        'fotos_video' => '#ec4899', 'carpeta_lista' => '#22c55e',
        'publicacion' => '#34d399', 'busqueda' => '#fbbf24', 'investigacion' => '#f97316',
        'contrato' => '#f472b6', 'entrega' => '#22d3ee', 'cierre' => '#10b981',
        'activo' => '#667eea', 'renovacion' => '#8b5cf6',
    ];

    const GUARANTEE_TYPES = [
        'deposito' => 'Deposito',
        'poliza_juridica' => 'Poliza Juridica',
        'fianza' => 'Fianza',
    ];

    const TYPES = [
        'venta' => 'Venta',
        'renta' => 'Renta',
        'captacion' => 'Captacion',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'monthly_rent' => 'decimal:2',
            'deposit_amount' => 'decimal:2',
            'commission_amount' => 'decimal:2',
            'commission_percentage' => 'decimal:2',
            'expected_close_date' => 'date',
            'lease_start_date' => 'date',
            'lease_end_date' => 'date',
            'closed_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    // --- Relationships ---
    public function property() { return $this->belongsTo(Property::class); }
    public function client() { return $this->belongsTo(Client::class); }
    public function secondaryClient() { return $this->belongsTo(Client::class, 'secondary_client_id'); }
    public function broker() { return $this->belongsTo(Broker::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function stageLogs() { return $this->hasMany(OperationStageLog::class)->orderByDesc('created_at'); }
    public function checklistItems() { return $this->hasMany(OperationChecklistItem::class); }
    public function tasks() { return $this->hasMany(Task::class); }
    public function documents() { return $this->hasMany(Document::class); }
    public function contracts() { return $this->hasMany(Contract::class); }
    public function poliza() { return $this->hasOne(PolizaJuridica::class); }
    public function commissions() { return $this->hasMany(Commission::class); }
    public function comments() { return $this->hasMany(OperationComment::class)->orderByDesc('created_at'); }
    public function sourceOperation() { return $this->belongsTo(Operation::class, 'source_operation_id'); }
    public function spawnedOperations() { return $this->hasMany(Operation::class, 'source_operation_id'); }

    public function currentStageChecklistItems()
    {
        return $this->checklistItems()->where('stage', $this->stage)->orderBy('id');
    }

    // --- Scopes ---
    public function scopeActive($q) { return $q->where('status', 'active'); }
    public function scopeByStage($q, string $stage) { return $q->where('stage', $stage); }
    public function scopeByType($q, string $type) { return $q->where('type', $type); }
    public function scopeVentas($q) { return $q->where('type', 'venta'); }
    public function scopeRentas($q) { return $q->where('type', 'renta'); }
    public function scopeCaptaciones($q) { return $q->where('type', 'captacion'); }

    public function scopeExpiringSoon($q, int $days = 30)
    {
        return $q->where('type', 'renta')
            ->where('status', 'active')
            ->where('stage', 'activo')
            ->whereNotNull('lease_end_date')
            ->where('lease_end_date', '<=', now()->addDays($days));
    }

    // --- Accessors ---
    public function getStageLabelAttribute(): string
    {
        return self::STAGES[$this->stage] ?? ucfirst($this->stage);
    }

    public function getStageColorAttribute(): string
    {
        return self::STAGE_COLORS[$this->stage] ?? '#94a3b8';
    }

    public function getPhaseLabelAttribute(): string
    {
        $phase = self::PHASE_MAP[$this->stage] ?? $this->phase;
        return $phase === 'captacion' ? 'Fase 1: Captacion' : 'Fase 2: Operacion';
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? ucfirst($this->type);
    }

    public function getGuaranteeTypeLabelAttribute(): string
    {
        return self::GUARANTEE_TYPES[$this->guarantee_type] ?? '';
    }

    public function getDaysUntilExpirationAttribute(): ?int
    {
        if (!$this->lease_end_date) return null;
        return (int) now()->startOfDay()->diffInDays($this->lease_end_date, false);
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->days_until_expiration !== null && $this->days_until_expiration < 0;
    }

    // --- Methods ---
    public function getAvailableStages(): array
    {
        $stages = match($this->type) {
            'captacion' => self::CAPTACION_STAGES,
            'renta' => self::RENTA_STAGES,
            default => self::VENTA_STAGES,
        };
        return array_intersect_key(self::STAGES, array_flip($stages));
    }

    public function getNextStage(): ?string
    {
        $stages = match($this->type) {
            'captacion' => self::CAPTACION_STAGES,
            'renta' => self::RENTA_STAGES,
            default => self::VENTA_STAGES,
        };
        $current = array_search($this->stage, $stages);
        if ($current === false || $current >= count($stages) - 1) return null;
        return $stages[$current + 1];
    }

    public function getPreviousStage(): ?string
    {
        $stages = match($this->type) {
            'captacion' => self::CAPTACION_STAGES,
            'renta' => self::RENTA_STAGES,
            default => self::VENTA_STAGES,
        };
        $current = array_search($this->stage, $stages);
        if ($current === false || $current <= 0) return null;
        return $stages[$current - 1];
    }
}
