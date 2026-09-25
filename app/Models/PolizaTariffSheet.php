<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Una hoja de servicios del proveedor (zona + año + gastos de emisión). Ej.: "Hoja de Servicios AM QRO NL 2026". */
class PolizaTariffSheet extends Model
{
    protected $fillable = ['name', 'zone_label', 'valid_year', 'emission_fee', 'is_active', 'notes'];

    protected function casts(): array
    {
        return ['emission_fee' => 'decimal:2', 'is_active' => 'boolean'];
    }

    public function rates() { return $this->hasMany(PolizaRate::class); }
}
