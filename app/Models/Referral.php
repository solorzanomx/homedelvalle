<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Referral extends Model
{
    protected $fillable = [
        'referrer_id', 'referral_type', 'referred_name', 'referred_phone', 'referred_context',
        'property_id', 'operation_id', 'client_id',
        'commission_percentage', 'commission_amount', 'status',
        'paid_at', 'transaction_id', 'notes', 'agreed_at',
    ];

    public const REFERRAL_TYPES = [
        'trajo_propietario' => 'Trajo propietario (5%)',
        'trajo_cliente' => 'Trajo cliente listo (10%)',
    ];

    public const STATUSES = [
        'registrado' => 'Registrado',
        'en_proceso' => 'En proceso',
        'por_pagar' => 'Por pagar',
        'pagado' => 'Pagado',
    ];

    public const STATUS_COLORS = [
        'registrado' => 'yellow',
        'en_proceso' => 'blue',
        'por_pagar' => 'orange',
        'pagado' => 'green',
    ];

    protected $casts = [
        'commission_percentage' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'agreed_at' => 'datetime',
    ];

    public function calculateCommission(): float
    {
        if (!$this->operation || !$this->operation->commission_amount) {
            return 0;
        }
        return round($this->operation->commission_amount * $this->commission_percentage / 100, 2);
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(Referrer::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function operation(): BelongsTo
    {
        return $this->belongsTo(Operation::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
}
