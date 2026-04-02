<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = ['user_id', 'deal_id', 'rental_process_id', 'operation_id', 'client_id', 'property_id', 'title', 'description', 'priority', 'status', 'due_date', 'completed_at'];
    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    public function user() { return $this->belongsTo(User::class); }
    public function deal() { return $this->belongsTo(Deal::class); }
    public function rentalProcess() { return $this->belongsTo(RentalProcess::class); }
    public function operation() { return $this->belongsTo(Operation::class); }
    public function client() { return $this->belongsTo(Client::class); }
    public function property() { return $this->belongsTo(Property::class); }

    public function scopePending($q) { return $q->where('status', 'pending'); }
    public function scopeOverdue($q) { return $q->where('status', '!=', 'completed')->where('due_date', '<', now()); }
}
