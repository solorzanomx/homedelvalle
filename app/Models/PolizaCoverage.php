<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Un concepto de la matriz de cobertura ("Consulta de antecedentes crediticios…"); qué planes lo incluyen vive en la tabla pivote. */
class PolizaCoverage extends Model
{
    public const SECTIONS = ['incluye' => 'Incluye', 'incumplimiento' => 'En caso de incumplimiento'];

    protected $fillable = ['section', 'label', 'note', 'sort_order'];

    public function plans()
    {
        return $this->belongsToMany(PolizaPlan::class, 'poliza_coverage_plan')->withPivot('included');
    }
}
