<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderCharge extends Model
{
    protected $fillable = [
        'operation_id', 'rental_process_id', 'provider_company_id', 'provider_contact_id',
        'flow', 'service_description', 'amount', 'commission_percentage',
        'status', 'paid_at', 'notes', 'created_by',
    ];

    public const FLOWS = [
        'cargo' => 'Cargo (nos cobra)',
        'comision' => 'Comisión (nos comisiona)',
    ];

    public const STATUSES = [
        'registrado' => 'Registrado',
        'confirmado' => 'Confirmado',
        'liquidado' => 'Liquidado',
    ];

    public const STATUS_COLORS = [
        'registrado' => 'yellow',
        'confirmado' => 'blue',
        'liquidado' => 'green',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'commission_percentage' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function operation(): BelongsTo
    {
        return $this->belongsTo(Operation::class);
    }

    public function rentalProcess(): BelongsTo
    {
        return $this->belongsTo(RentalProcess::class);
    }

    public function providerCompany(): BelongsTo
    {
        return $this->belongsTo(ProviderCompany::class);
    }

    public function providerContact(): BelongsTo
    {
        return $this->belongsTo(ProviderContact::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Mismo mecanismo que Referral::calculateCommission() — % sobre la comisión de la Operation. */
    public function calculateCommission(): float
    {
        if (!$this->operation || !$this->operation->commission_amount || !$this->commission_percentage) {
            return 0;
        }
        return round($this->operation->commission_amount * $this->commission_percentage / 100, 2);
    }

    public function getFlowLabelAttribute(): string
    {
        return self::FLOWS[$this->flow] ?? ucfirst($this->flow);
    }

    public function getStatusLabelAttribute(): string
    {
        // La etiqueta depende de la dirección: "cargo" es dinero que
        // nosotros debemos pagar, "comision" es dinero que nos deben a
        // nosotros — mismo registro neutral de status, distinta lectura.
        if ($this->status === 'liquidado') {
            return $this->flow === 'cargo' ? 'Pagado' : 'Cobrado';
        }
        if ($this->status === 'confirmado') {
            return $this->flow === 'cargo' ? 'Por pagar' : 'Por cobrar';
        }
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    public function getStatusColorAttribute(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'yellow';
    }
}
