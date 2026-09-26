<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientReference extends Model
{
    protected $fillable = [
        'client_id',
        'name',
        'address',
        'mobile_phone',
        'landline_phone',
        'email',
        'sort_order',
        'status',
        'rejection_reason',
        'rejected_at',
    ];

    protected function casts(): array
    {
        return ['rejected_at' => 'datetime'];
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /** Las que cuentan para las 3 requeridas (una rechazada NO cuenta hasta que se reemplace). */
    public function scopeValid($query)
    {
        return $query->where(fn($q) => $q->whereNull('status')->orWhere('status', '!=', 'rejected'));
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
