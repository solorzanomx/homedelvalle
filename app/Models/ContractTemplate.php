<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractTemplate extends Model
{
    protected $fillable = ['name', 'type', 'body', 'variables', 'is_active'];
    const TYPES = [
        'rental' => 'Contrato de Arrendamiento',
        'commission' => 'Contrato de Comision',
        'renewal' => 'Renovacion de Contrato',
    ];

    // Default variables available for all templates
    const DEFAULT_VARIABLES = [
        '{{propietario_nombre}}' => 'Nombre del propietario',
        '{{propietario_email}}' => 'Email del propietario',
        '{{propietario_telefono}}' => 'Telefono del propietario',
        '{{inquilino_nombre}}' => 'Nombre del inquilino',
        '{{inquilino_email}}' => 'Email del inquilino',
        '{{inquilino_telefono}}' => 'Telefono del inquilino',
        '{{propiedad_titulo}}' => 'Titulo de la propiedad',
        '{{propiedad_direccion}}' => 'Direccion de la propiedad',
        '{{renta_mensual}}' => 'Monto de renta mensual',
        '{{moneda}}' => 'Moneda (MXN/USD)',
        '{{deposito}}' => 'Monto del deposito',
        '{{duracion_meses}}' => 'Duracion en meses',
        '{{fecha_inicio}}' => 'Fecha inicio contrato',
        '{{fecha_fin}}' => 'Fecha fin contrato',
        '{{comision_monto}}' => 'Monto de comision',
        '{{comision_porcentaje}}' => 'Porcentaje de comision',
        '{{garantia_tipo}}' => 'Tipo de garantia',
        '{{broker_nombre}}' => 'Nombre del broker',
        '{{fecha_actual}}' => 'Fecha actual',
        '{{empresa_nombre}}' => 'Nombre de la empresa',
    ];

    protected function casts(): array
    {
        return [
            'variables' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function contracts() { return $this->hasMany(Contract::class); }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? ucfirst($this->type);
    }

    public function scopeActive($q) { return $q->where('is_active', true); }
    public function scopeOfType($q, string $type) { return $q->where('type', $type); }
}
