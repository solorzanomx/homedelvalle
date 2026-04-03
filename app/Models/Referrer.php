<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Referrer extends Model
{
    protected $fillable = [
        'name', 'phone', 'email', 'type',
        'address', 'notes', 'status',
        'total_referrals', 'total_earned',
    ];

    protected $casts = [
        'total_earned' => 'decimal:2',
    ];

    public const TYPES = [
        'portero' => 'Portero',
        'vecino' => 'Vecino',
        'broker_hipotecario' => 'Broker Hipotecario',
        'cliente_pasado' => 'Cliente Pasado',
        'comisionista' => 'Comisionista',
        'otro' => 'Otro',
    ];

    public function referrals(): HasMany
    {
        return $this->hasMany(Referral::class);
    }
}
