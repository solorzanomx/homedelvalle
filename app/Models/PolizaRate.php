<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Tarifa de UN plan para un rango de renta mensual: (rent_over, rent_up_to] → precio fijo, o % de la renta si no hay tope. */
class PolizaRate extends Model
{
    protected $fillable = ['poliza_tariff_sheet_id', 'poliza_plan_id', 'rent_over', 'rent_up_to', 'fixed_price', 'percent', 'sort_order'];

    protected function casts(): array
    {
        return ['rent_over' => 'decimal:2', 'rent_up_to' => 'decimal:2', 'fixed_price' => 'decimal:2', 'percent' => 'decimal:2'];
    }

    public function sheet() { return $this->belongsTo(PolizaTariffSheet::class, 'poliza_tariff_sheet_id'); }
    public function plan() { return $this->belongsTo(PolizaPlan::class, 'poliza_plan_id'); }
}
