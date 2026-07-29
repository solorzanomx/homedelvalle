<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ZonificacionPredio extends Model
{
    protected $fillable = [
        'alcaldia', 'calle', 'no_externo', 'colonia', 'codigo_postal',
        'superficie', 'uso_descri', 'densidad_d', 'niveles', 'altura',
        'area_libre', 'minimo_viv', 'liga_ciudadana', 'cuenta_catastral',
        'longitud', 'latitud',
    ];

    protected $casts = [
        'superficie' => 'decimal:2',
        'longitud' => 'decimal:8',
        'latitud' => 'decimal:8',
    ];
}
