<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyMarketingStrategy extends Model
{
    protected $fillable = [
        'operation_id', 'target_audience', 'positioning_summary', 'recommended_channels',
        'key_selling_points', 'raw_ai_response', 'generated_at', 'approved_at', 'approved_by',
    ];

    protected function casts(): array
    {
        return [
            'target_audience'      => 'array',
            'recommended_channels'  => 'array',
            'key_selling_points'    => 'array',
            'raw_ai_response'       => 'array',
            'generated_at'          => 'datetime',
            'approved_at'           => 'datetime',
        ];
    }

    public function operation()
    {
        return $this->belongsTo(Operation::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isApproved(): bool
    {
        return !is_null($this->approved_at);
    }
}
