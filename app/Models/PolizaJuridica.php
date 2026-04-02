<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'rental_process_id', 'operation_id', 'tenant_client_id', 'insurance_company', 'policy_number',
    'status', 'submitted_at', 'review_started_at', 'resolved_at', 'rejection_reason',
    'cost', 'currency', 'coverage_start', 'coverage_end', 'notes',
])]
class PolizaJuridica extends Model
{
    const STATUSES = [
        'pending' => 'Pendiente',
        'documents_submitted' => 'Documentos Enviados',
        'in_review' => 'En Revision',
        'approved' => 'Aprobada',
        'rejected' => 'Rechazada',
        'expired' => 'Expirada',
    ];

    const STATUS_COLORS = [
        'pending' => '#94a3b8',
        'documents_submitted' => '#3b82f6',
        'in_review' => '#f59e0b',
        'approved' => '#10b981',
        'rejected' => '#ef4444',
        'expired' => '#6b7280',
    ];

    protected function casts(): array
    {
        return [
            'cost' => 'decimal:2',
            'submitted_at' => 'datetime',
            'review_started_at' => 'datetime',
            'resolved_at' => 'datetime',
            'coverage_start' => 'date',
            'coverage_end' => 'date',
        ];
    }

    public function rentalProcess() { return $this->belongsTo(RentalProcess::class); }
    public function operation() { return $this->belongsTo(Operation::class); }
    public function tenantClient() { return $this->belongsTo(Client::class, 'tenant_client_id'); }
    public function events() { return $this->hasMany(PolizaEvent::class)->orderByDesc('created_at'); }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    public function getStatusColorAttribute(): string
    {
        return self::STATUS_COLORS[$this->status] ?? '#94a3b8';
    }

    public function getIsActiveAttribute(): bool
    {
        return in_array($this->status, ['approved']) && (!$this->coverage_end || !$this->coverage_end->isPast());
    }
}
