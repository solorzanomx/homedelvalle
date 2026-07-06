<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyPortalReport extends Model
{
    const PORTALS = [
        'inmuebles24' => 'Inmuebles24',
    ];

    protected $fillable = [
        'property_id', 'portal', 'external_listing_id',
        'week_start', 'week_end',
        'exposicion', 'visualizaciones', 'consultas_recibidas',
        'completaron_formulario', 'contactaron_whatsapp', 'vieron_datos',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'week_start' => 'date',
            'week_end' => 'date',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getPortalLabelAttribute(): string
    {
        return self::PORTALS[$this->portal] ?? ucfirst($this->portal);
    }
}
