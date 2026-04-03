<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Broker extends Model
{
    protected $fillable = [
        'name', 'email', 'phone', 'license_number',
        'commission_rate', 'company_name', 'bio', 'status', 'photo',
        'broker_company_id', 'type', 'specialty', 'referral_source',
    ];

    protected $casts = [
        'commission_rate' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(BrokerCompany::class, 'broker_company_id');
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    public function properties(): HasMany
    {
        return $this->hasMany(Property::class);
    }

    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class);
    }

    public function operations(): HasMany
    {
        return $this->hasMany(Operation::class);
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(Commission::class);
    }
}
