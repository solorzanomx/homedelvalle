<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ValuationLead extends Model
{
    protected $fillable = [
        'colonia_id', 'colonia_raw', 'property_type', 'm2_approx',
        'owner_name', 'owner_phone', 'owner_email', 'message',
        'source_page', 'utm_source', 'utm_medium', 'utm_campaign',
        'status', 'converted_property_id', 'assigned_to',
    ];

    protected $casts = ['m2_approx' => 'decimal:2'];

    public function colonia(): BelongsTo
    {
        return $this->belongsTo(MarketColonia::class, 'colonia_id');
    }

    public function convertedProperty(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'converted_property_id');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'new'       => 'Nuevo',
            'contacted' => 'Contactado',
            'qualified' => 'Calificado',
            'converted' => 'Convertido',
            'discarded' => 'Descartado',
            default     => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'new'       => 'blue',
            'contacted' => 'yellow',
            'qualified' => 'purple',
            'converted' => 'green',
            'discarded' => 'red',
            default     => 'yellow',
        };
    }
}
