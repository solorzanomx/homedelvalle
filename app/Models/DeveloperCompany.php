<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeveloperCompany extends Model
{
    protected $fillable = [
        'name', 'type', 'rfc', 'website', 'notes', 'status',
    ];

    public const TYPES = [
        'desarrolladora'  => 'Desarrolladora',
        'constructora'    => 'Constructora',
        'fondo_inversion' => 'Fondo de Inversión',
        'otro'            => 'Otro',
    ];

    public function contacts(): HasMany
    {
        return $this->hasMany(DeveloperContact::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? ucfirst($this->type);
    }
}
