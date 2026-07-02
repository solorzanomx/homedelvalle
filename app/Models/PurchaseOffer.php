<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOffer extends Model
{
    protected $fillable = [
        'operation_id', 'precio_ofertado', 'monto_apartado', 'pago_firma_contrato',
        'pago_firma_escritura', 'forma_pago', 'vigencia_dias', 'folio_real',
        'comentarios', 'status', 'offered_at', 'last_pdf_path',
    ];

    const STATUS_LABELS = [
        'pending'  => 'Pendiente',
        'accepted' => 'Aceptada',
        'rejected' => 'Rechazada',
        'expired'  => 'Vencida',
    ];

    protected function casts(): array
    {
        return [
            'precio_ofertado'      => 'decimal:2',
            'monto_apartado'       => 'decimal:2',
            'pago_firma_contrato'  => 'decimal:2',
            'pago_firma_escritura' => 'decimal:2',
            'offered_at'           => 'datetime',
        ];
    }

    public function operation(): BelongsTo
    {
        return $this->belongsTo(Operation::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function getVigenteHastaAttribute(): \Illuminate\Support\Carbon
    {
        return $this->offered_at->copy()->addDays($this->vigencia_dias);
    }
}
