<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProviderCompany extends Model
{
    protected $fillable = [
        'name', 'type', 'contact_name', 'email', 'phone',
        'address', 'city', 'notes', 'status',
    ];

    public const TYPES = [
        'notaria' => 'Notaría',
        'poliza_juridica' => 'Aseguradora / Póliza Jurídica',
        'limpieza' => 'Limpieza',
        'mantenimiento' => 'Mantenimiento',
        'fotografia_video' => 'Fotografía / Video',
        'legal' => 'Legal',
        'contabilidad' => 'Contabilidad',
        'otro' => 'Otro',
    ];

    public function contacts(): HasMany
    {
        return $this->hasMany(ProviderContact::class);
    }

    public function charges(): HasMany
    {
        return $this->hasMany(ProviderCharge::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? ucfirst($this->type);
    }
}
