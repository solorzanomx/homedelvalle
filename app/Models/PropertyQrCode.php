<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyQrCode extends Model
{
    protected $fillable = [
        'property_id',
        'qr_code_path',
        'qr_url',
        'generated_at',
    ];

    protected $casts = [
        'generated_at' => 'datetime',
    ];

    /**
     * Relación con Property
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Verificar si el QR necesita regeneración
     * Retorna true si la URL cambió
     */
    public function needsRegeneration(string $newUrl): bool
    {
        return $this->qr_url !== $newUrl;
    }
}

