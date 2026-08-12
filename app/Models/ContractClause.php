<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractClause extends Model
{
    protected $fillable = ['clauseable_type', 'clauseable_id', 'key', 'title', 'body', 'section', 'sort_order', 'is_locked'];

    const SECTIONS = [
        'caratula' => 'Carátula',
        'declaracion' => 'Declaración',
        'clausula' => 'Cláusula',
        'firma' => 'Firma',
    ];

    protected function casts(): array
    {
        return [
            'is_locked' => 'boolean',
        ];
    }

    public function clauseable()
    {
        return $this->morphTo();
    }
}
