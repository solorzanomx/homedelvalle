<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StageChecklistTemplate extends Model
{
    protected $fillable = ['operation_type', 'stage', 'title', 'description', 'sort_order', 'is_required', 'is_active'];
    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function checklistItems() { return $this->hasMany(OperationChecklistItem::class); }

    public function scopeActive($q) { return $q->where('is_active', true); }

    public function scopeForStage($q, string $stage, string $operationType)
    {
        $types = $operationType === 'captacion'
            ? ['captacion']
            : [$operationType, 'both'];

        return $q->where('stage', $stage)
            ->whereIn('operation_type', $types)
            ->where('is_active', true)
            ->orderBy('sort_order');
    }
}
