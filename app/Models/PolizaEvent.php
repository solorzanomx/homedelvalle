<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PolizaEvent extends Model
{
    protected $fillable = ['poliza_juridica_id', 'user_id', 'event_type', 'description', 'data'];
    protected function casts(): array
    {
        return [
            'data' => 'array',
        ];
    }

    public function polizaJuridica() { return $this->belongsTo(PolizaJuridica::class); }
    public function user() { return $this->belongsTo(User::class); }
}
