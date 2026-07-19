<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiUsageLog extends Model
{
    protected $fillable = ['service', 'provider', 'model', 'input_tokens', 'output_tokens', 'cost_usd', 'related_type', 'related_id'];

    protected function casts(): array
    {
        return [
            'cost_usd' => 'decimal:4',
        ];
    }

    public function related() { return $this->morphTo(); }
}
