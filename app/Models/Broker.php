<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Broker extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'license_number',
        'commission_rate',
        'company_name',
        'bio',
        'status',
        'photo',
    ];

    protected $casts = [
        'commission_rate' => 'decimal:2',
    ];

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    public function properties(): HasMany
    {
        return $this->hasMany(Property::class);
    }
}
