<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RentalPagare extends Model
{
    protected $fillable = [
        'rental_process_id',
        'quantity',
        'amount_each',
        'currency',
        'issue_date',
        'beneficiary_name',
        'status',
        'notes',
    ];

    protected $casts = [
        'issue_date'   => 'date',
        'amount_each'  => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function rentalProcess(): BelongsTo
    {
        return $this->belongsTo(RentalProcess::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'  => 'Pendientes de firma',
            'signed'   => 'Firmados',
            'held'     => 'En custodia',
            'returned' => 'Devueltos',
            default    => $this->status,
        };
    }
}
