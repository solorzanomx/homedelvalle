<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyDevelopmentProfile extends Model
{
    protected $fillable = [
        'property_id',
        'frente', 'fondo', 'forma_terreno',
        'uso_suelo', 'zonificacion_key', 'cos', 'cus', 'niveles_permitidos',
        'restricciones', 'colindancias', 'servicios', 'libre_gravamen', 'situacion_legal',
        'notes',
    ];

    protected $casts = [
        'frente' => 'decimal:2',
        'fondo' => 'decimal:2',
        'cos' => 'decimal:2',
        'cus' => 'decimal:2',
        'niveles_permitidos' => 'integer',
        'libre_gravamen' => 'boolean',
    ];

    public const FORMAS = [
        'rectangular'  => 'Rectangular',
        'irregular'    => 'Irregular',
        'trapezoidal'  => 'Trapezoidal',
        'en_escuadra'  => 'En escuadra',
        'otro'         => 'Otro',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function getZonificacionLabelAttribute(): ?string
    {
        return \App\Services\ConstructorValuationService::ZONIFICACIONES[$this->zonificacion_key]['label'] ?? null;
    }

    public function getFormaLabelAttribute(): string
    {
        return self::FORMAS[$this->forma_terreno] ?? ($this->forma_terreno ?: '—');
    }
}
