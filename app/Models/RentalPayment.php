<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RentalPayment extends Model
{
    protected $fillable = [
        'rental_process_id', 'period', 'amount',
        'status', 'paid_at', 'notes', 'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'period'  => 'date',
            'paid_at' => 'date',
            'amount'  => 'decimal:2',
        ];
    }

    const STATUSES = [
        'pending' => 'Pendiente',
        'paid'    => 'Pagado',
        'late'    => 'Atrasado',
        'waived'  => 'Condonado',
    ];

    public function rentalProcess() { return $this->belongsTo(RentalProcess::class); }
    public function recordedBy()    { return $this->belongsTo(User::class, 'recorded_by'); }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'paid'   => '#10b981',
            'late'   => '#ef4444',
            'waived' => '#94a3b8',
            default  => '#f59e0b',
        };
    }
}
