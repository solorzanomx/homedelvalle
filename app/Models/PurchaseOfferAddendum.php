<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOfferAddendum extends Model
{
    protected $fillable = [
        'purchase_offer_id', 'numero', 'contrato_nombre', 'contrato_fecha',
        'comision_amount', 'comision_esquema',
        'comision_firma_contrato', 'comision_firma_escritura',
        'representative_user_id', 'last_pdf_path',
    ];

    protected function casts(): array
    {
        return [
            'numero'                   => 'integer',
            'contrato_fecha'           => 'date',
            'comision_amount'          => 'decimal:2',
            'comision_firma_contrato'  => 'decimal:2',
            'comision_firma_escritura' => 'decimal:2',
        ];
    }

    public function purchaseOffer(): BelongsTo
    {
        return $this->belongsTo(PurchaseOffer::class);
    }

    public function representative(): BelongsTo
    {
        return $this->belongsTo(User::class, 'representative_user_id');
    }
}
