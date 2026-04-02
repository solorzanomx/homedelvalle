<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MarketingCampaign extends Model
{
    protected $fillable = ['marketing_channel_id', 'name', 'budget', 'spent', 'currency', 'start_date', 'end_date', 'status', 'notes'];
    protected function casts(): array
    {
        return [
            'budget' => 'decimal:2',
            'spent' => 'decimal:2',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(MarketingChannel::class, 'marketing_channel_id');
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function getBudgetRemainingAttribute(): float
    {
        return max(0, $this->budget - $this->spent);
    }

    public function getBudgetPacingAttribute(): float
    {
        if ($this->budget <= 0) return 0;
        return round(($this->spent / $this->budget) * 100, 1);
    }
}
