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
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
