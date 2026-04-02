<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Deal extends Model
{
    protected $fillable = ['property_id', 'client_id', 'broker_id', 'stage', 'amount', 'commission_amount', 'notes', 'expected_close_date', 'closed_at'];
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'commission_amount' => 'decimal:2',
            'expected_close_date' => 'date',
            'closed_at' => 'datetime',
        ];
    }

    public function property() { return $this->belongsTo(Property::class); }
    public function client() { return $this->belongsTo(Client::class); }
    public function broker() { return $this->belongsTo(Broker::class); }
    public function commissions() { return $this->hasMany(Commission::class); }
    public function tasks() { return $this->hasMany(Task::class); }

    public function scopeActive($q) { return $q->whereNotIn('stage', ['won', 'lost']); }
    public function scopeWon($q) { return $q->where('stage', 'won'); }
    public function scopeLost($q) { return $q->where('stage', 'lost'); }
    public function scopeByStage($q, $stage) { return $q->where('stage', $stage); }
}
