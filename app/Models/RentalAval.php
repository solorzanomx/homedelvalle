<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RentalAval extends Model
{
    protected $fillable = [
        'rental_process_id',
        'client_id',
        'name',
        'curp',
        'rfc',
        'phone',
        'email',
        'relationship',
        'id_type',
        'id_number',
        'id_expiry',
        'property_address',
        'property_colony',
        'property_municipality',
        'property_state',
        'property_zip',
        'property_folio_real',
        'property_value',
        'property_has_mortgage',
        'property_free_of_liens',
        'notes',
    ];

    protected $casts = [
        'id_expiry'              => 'date',
        'property_value'         => 'decimal:2',
        'property_has_mortgage'  => 'boolean',
        'property_free_of_liens' => 'boolean',
    ];

    public function rentalProcess(): BelongsTo
    {
        return $this->belongsTo(RentalProcess::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
