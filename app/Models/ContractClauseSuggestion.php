<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractClauseSuggestion extends Model
{
    protected $fillable = [
        'contract_id', 'contract_clause_id', 'suggestion_type',
        'proposed_title', 'proposed_body', 'rationale', 'status',
        'requested_by', 'reviewed_by', 'reviewed_at',
    ];

    const TYPES = [
        'edit' => 'Editar',
        'add' => 'Agregar',
        'remove' => 'Eliminar',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
        ];
    }

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function clause()
    {
        return $this->belongsTo(ContractClause::class, 'contract_clause_id');
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->suggestion_type] ?? ucfirst($this->suggestion_type);
    }
}
