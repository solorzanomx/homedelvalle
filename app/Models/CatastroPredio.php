<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatastroPredio extends Model
{
    protected $fillable = [
        'fid', 'fid_2', 'calle_numero', 'calle', 'numero', 'codigo_postal',
        'colonia', 'alcaldia', 'sup_terreno', 'sup_construccion',
        'anio_construccion', 'instal_esp', 'valor_unitario_suelo',
        'valor_suelo', 'cve_vus', 'subsidio',
    ];

    protected $casts = [
        'sup_terreno' => 'decimal:2',
        'sup_construccion' => 'decimal:2',
        'valor_unitario_suelo' => 'decimal:2',
        'valor_suelo' => 'decimal:2',
    ];
}
