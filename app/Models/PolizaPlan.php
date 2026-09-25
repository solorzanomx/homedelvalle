<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Esquema de póliza jurídica de un proveedor (hoy Previsión Legal). Es un
 * CATÁLOGO editable desde el CRM (nombre, precio, qué incluye): si el proveedor
 * cambia precios o coberturas no hay que programar. El Portal solo muestra los
 * planes activos CON precio.
 */
class PolizaPlan extends Model
{
    protected $fillable = ['provider_name', 'name', 'tagline', 'price', 'currency', 'inclusions', 'description', 'sort_order', 'is_active', 'is_recommended', 'show_on_website', 'show_price_public'];

    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'inclusions' => 'array', 'is_active' => 'boolean', 'is_recommended' => 'boolean', 'show_on_website' => 'boolean', 'show_price_public' => 'boolean'];
    }

    /** Planes que el inquilino puede ver y elegir. */
    public function scopeOffered($q)
    {
        return $q->where('is_active', true)->orderBy('sort_order')->orderBy('id');
    }

    /** Planes de la página pública (incluye los que aún no tienen precio: la cobertura ya es información útil). */
    public function scopeForWebsite($q)
    {
        return $q->where('show_on_website', true)->orderBy('sort_order')->orderBy('id');
    }

    public function coverages()
    {
        return $this->belongsToMany(PolizaCoverage::class, 'poliza_coverage_plan')->withPivot('included')->orderBy('poliza_coverages.sort_order');
    }

    public function rates() { return $this->hasMany(PolizaRate::class); }

    /** Conceptos de la matriz que ESTE plan incluye (en el orden de la hoja). */
    public function includedCoverages()
    {
        return $this->coverages->filter(fn($c) => (bool) $c->pivot->included)->values();
    }

    public function getPriceFormattedAttribute(): string
    {
        return $this->price !== null ? '$' . number_format((float) $this->price, 0) . ' ' . $this->currency : 'Precio por confirmar';
    }
}
