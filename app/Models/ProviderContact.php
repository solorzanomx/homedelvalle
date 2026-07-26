<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderContact extends Model
{
    protected $fillable = [
        'provider_company_id', 'name', 'role', 'email', 'phone', 'status', 'birth_date',
    ];

    protected $casts = [
        'birth_date' => 'date',
    ];

    public function providerCompany(): BelongsTo
    {
        return $this->belongsTo(ProviderCompany::class);
    }
}
