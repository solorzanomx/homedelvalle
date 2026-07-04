<?php

namespace App\Models;

use App\Observers\OperationObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy(OperationObserver::class)]
class Operation extends Model
{
    protected $fillable = ['type', 'intent', 'target_type', 'phase', 'stage', 'status', 'property_id', 'client_id', 'form_submission_id', 'secondary_client_id', 'broker_id', 'user_id', 'source_operation_id', 'amount', 'monthly_rent', 'currency', 'deposit_amount', 'commission_amount', 'commission_percentage', 'guarantee_type', 'expected_close_date', 'lease_start_date', 'lease_end_date', 'lease_duration_months', 'notes', 'closed_at', 'completed_at', 'cancelled_at',];
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
        'candidatos' => 'Candidatos',
        'oferta_aceptada' => 'Oferta Aceptada',
        'investigacion' => 'Investigacion',
        'contrato' => 'Contrato',
        'entrega' => 'Entrega',
        'cierre' => 'Cierre',
        'activo' => 'Activo',
        'renovacion' => 'Renovacion',
        'precalificacion' => 'Precalificacion',
        'listo' => 'Listo para ofertar',
    ];

    // Sin lead/contacto/visita/exclusiva: toda venta nace de una captacion que ya
    // recorrio esas etapas por su cuenta (CAPTACION_STAGES) — no existe (ni se
    // permite crear manualmente) una venta "comprador directo" sin captacion previa.
    const VENTA_STAGES = ['mejoras','fotos_video','carpeta_lista','publicacion','candidatos','oferta_aceptada','investigacion','contrato','entrega','cierre'];
    const RENTA_STAGES = ['lead','contacto','visita','exclusiva','mejoras','fotos_video','carpeta_lista','publicacion','busqueda','investigacion','contrato','entrega','cierre','activo','renovacion'];
    const CAPTACION_STAGES = ['lead','contacto','visita','revision_docs','avaluo','exclusiva'];
    // Pipeline de calificación del comprador ANTES de que haga una oferta real
    // (eso ya vive como PurchaseOffer sobre la Operation del vendedor) — termina
    // en 'listo', no genera ninguna Operation nueva automáticamente.
    const COMPRADOR_STAGES = ['lead','contacto','visita','precalificacion','listo'];

    const PHASE_MAP = [
        'lead' => 'captacion', 'contacto' => 'captacion', 'visita' => 'captacion', 'exclusiva' => 'captacion',
        'revision_docs' => 'captacion', 'avaluo' => 'captacion',
        // mejoras/fotos_video/carpeta_lista viven en VENTA_STAGES/RENTA_STAGES desde el
        // reordenamiento del 2026-07-01 (ver project_homedelvalle_flujo_captacion.md) —
        // 'captacion' aquí era un residuo de antes de ese cambio.
        'mejoras' => 'operacion', 'fotos_video' => 'operacion', 'carpeta_lista' => 'operacion',
        'publicacion' => 'operacion', 'busqueda' => 'operacion', 'candidatos' => 'operacion', 'oferta_aceptada' => 'operacion',
        'investigacion' => 'operacion',
        'contrato' => 'operacion', 'entrega' => 'operacion', 'cierre' => 'operacion',
        'activo' => 'operacion', 'renovacion' => 'operacion',
        'precalificacion' => 'operacion', 'listo' => 'operacion',
    ];

    const STAGE_COLORS = [
        'lead' => '#94a3b8', 'contacto' => '#60a5fa', 'visita' => '#818cf8', 'exclusiva' => '#a78bfa',
        'revision_docs' => '#f59e0b', 'avaluo' => '#ef4444', 'mejoras' => '#14b8a6',
        'fotos_video' => '#ec4899', 'carpeta_lista' => '#22c55e',
        'publicacion' => '#34d399', 'busqueda' => '#fbbf24', 'candidatos' => '#fbbf24', 'oferta_aceptada' => '#16a34a',
        'investigacion' => '#f97316',
        'contrato' => '#f472b6', 'entrega' => '#22d3ee', 'cierre' => '#10b981',
        'activo' => '#667eea', 'renovacion' => '#8b5cf6',
        'precalificacion' => '#0ea5e9', 'listo' => '#22c55e',
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
        'comprador' => 'Comprador',
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
    public function formSubmission() { return $this->belongsTo(FormSubmission::class); }
    public function secondaryClient() { return $this->belongsTo(Client::class, 'secondary_client_id'); }
    public function broker() { return $this->belongsTo(Broker::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function stageLogs() { return $this->hasMany(OperationStageLog::class)->orderByDesc('created_at'); }
    public function checklistItems() { return $this->hasMany(OperationChecklistItem::class); }
    public function tasks() { return $this->hasMany(Task::class); }
    public function documents() { return $this->hasMany(Document::class); }
    public function purchaseOffers() { return $this->hasMany(PurchaseOffer::class); }
    public function contracts() { return $this->hasMany(Contract::class); }
    public function poliza() { return $this->hasOne(PolizaJuridica::class); }
    public function commissions() { return $this->hasMany(Commission::class); }
    public function referrals() { return $this->hasMany(Referral::class); }
    public function comments() { return $this->hasMany(OperationComment::class)->orderByDesc('created_at'); }
    public function sourceOperation() { return $this->belongsTo(Operation::class, 'source_operation_id'); }
    public function spawnedOperations() { return $this->hasMany(Operation::class, 'source_operation_id'); }
    public function rentalProcess() { return $this->hasOne(RentalProcess::class); }
    public function marketingStrategy() { return $this->hasOne(PropertyMarketingStrategy::class); }
    public function expenses() { return $this->hasMany(OperationExpense::class); }

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

    /** Lista de etapas válidas para un type dado — única fuente de verdad,
     * usada tanto por las instancias (abajo) como por OperationObserver
     * para rechazar combinaciones type/stage que nunca son válidas. */
    public static function stagesForType(string $type): array
    {
        return match($type) {
            'captacion' => self::CAPTACION_STAGES,
            'renta' => self::RENTA_STAGES,
            'comprador' => self::COMPRADOR_STAGES,
            default => self::VENTA_STAGES,
        };
    }

    public function getAvailableStages(): array
    {
        $stages = self::stagesForType($this->type);
        return array_intersect_key(self::STAGES, array_flip($stages));
    }

    public function getNextStage(): ?string
    {
        $stages = self::stagesForType($this->type);
        $current = array_search($this->stage, $stages);
        if ($current === false || $current >= count($stages) - 1) return null;
        return $stages[$current + 1];
    }

    public function getPreviousStage(): ?string
    {
        $stages = self::stagesForType($this->type);
        $current = array_search($this->stage, $stages);
        if ($current === false || $current <= 0) return null;
        return $stages[$current - 1];
    }
}
