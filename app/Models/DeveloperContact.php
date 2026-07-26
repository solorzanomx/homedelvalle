<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeveloperContact extends Model
{
    protected $fillable = [
        'developer_company_id', 'name', 'role', 'email', 'phone',
        'interest_zones', 'budget_min', 'budget_max', 'notes', 'status',
    ];

    protected $casts = [
        'interest_zones' => 'array',
        'budget_min' => 'decimal:2',
        'budget_max' => 'decimal:2',
    ];

    public function developerCompany(): BelongsTo
    {
        return $this->belongsTo(DeveloperCompany::class);
    }

    public function isIndependent(): bool
    {
        return is_null($this->developer_company_id);
    }
}
