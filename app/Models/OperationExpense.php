<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperationExpense extends Model
{
    protected $fillable = ['operation_id', 'category', 'description', 'amount', 'created_by'];

    const CATEGORIES = [
        'fotografia' => 'Fotografía / Video',
        'publicidad' => 'Publicidad pagada',
        'staging'    => 'Staging / Mejoras',
        'otro'       => 'Otro',
    ];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2'];
    }

    public function operation()
    {
        return $this->belongsTo(Operation::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }
}
