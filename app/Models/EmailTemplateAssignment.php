<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailTemplateAssignment extends Model
{
    protected $fillable = [
        'template_id',
        'trigger_type',
        'trigger_name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(CustomEmailTemplate::class, 'template_id');
    }

    public function toggleActive(): void
    {
        $this->update(['is_active' => !$this->is_active]);
    }

    public function activate(): void
    {
        $this->update(['is_active' => true]);
    }

    public function deactivate(): void
    {
        $this->update(['is_active' => false]);
    }
}
