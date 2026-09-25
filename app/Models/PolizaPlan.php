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
    protected $fillable = ['provider_name', 'name', 'tagline', 'price', 'currency', 'inclusions', 'description', 'sort_order', 'is_active', 'is_recommended'];

    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'inclusions' => 'array', 'is_active' => 'boolean', 'is_recommended' => 'boolean'];
    }

    /** Planes que el inquilino puede ver y elegir. */
    public function scopeOffered($q)
    {
        return $q->where('is_active', true)->whereNotNull('price')->orderBy('sort_order')->orderBy('id');
    }

    public function getPriceFormattedAttribute(): string
    {
        return $this->price !== null ? '$' . number_format((float) $this->price, 0) . ' ' . $this->currency : 'Precio por confirmar';
    }
}
