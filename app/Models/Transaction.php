<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['type', 'category', 'description', 'amount', 'currency', 'date', 'deal_id', 'property_id', 'broker_id', 'user_id', 'payment_method', 'reference', 'notes'])]
class Transaction extends Model
{
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'date' => 'date',
        ];
    }

    public function deal() { return $this->belongsTo(Deal::class); }
    public function property() { return $this->belongsTo(Property::class); }
    public function broker() { return $this->belongsTo(Broker::class); }
    public function user() { return $this->belongsTo(User::class); }

    public function scopeIncome($q) { return $q->where('type', 'income'); }
    public function scopeExpense($q) { return $q->where('type', 'expense'); }
}
