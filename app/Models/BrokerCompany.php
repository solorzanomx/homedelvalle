<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BrokerCompany extends Model
{
    protected $fillable = [
        'name', 'contact_name', 'email', 'phone',
        'address', 'city', 'website', 'logo', 'notes', 'status',
    ];

    public function brokers(): HasMany
    {
        return $this->hasMany(Broker::class, 'broker_company_id');
    }
}
