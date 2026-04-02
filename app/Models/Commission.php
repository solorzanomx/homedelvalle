<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commission extends Model
{
    protected $fillable = ['deal_id', 'operation_id', 'broker_id', 'amount', 'percentage', 'status', 'paid_at', 'transaction_id', 'notes'];
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'percentage' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function deal() { return $this->belongsTo(Deal::class); }
    public function operation() { return $this->belongsTo(Operation::class); }
    public function broker() { return $this->belongsTo(Broker::class); }
    public function transaction() { return $this->belongsTo(Transaction::class); }
}
