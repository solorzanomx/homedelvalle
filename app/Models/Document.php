<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'rental_process_id', 'operation_id', 'client_id', 'property_id', 'uploaded_by',
    'category', 'label', 'file_path', 'file_name', 'mime_type', 'file_size',
    'status', 'rejection_reason', 'verified_at', 'verified_by',
])]
class Document extends Model
{
    const CATEGORIES = [
        'commission_contract' => 'Contrato de Comision',
        'escritura' => 'Escritura',
        'predial' => 'Predial',
        'owner_id' => 'INE Propietario',
        'tenant_id' => 'INE Arrendatario',
        'proof_of_income' => 'Comprobante de Ingresos',
        'credit_report' => 'Reporte Crediticio',
        'references' => 'Referencias',
        'rental_contract' => 'Contrato de Arrendamiento',
        'poliza_contract' => 'Poliza Juridica',
        'other' => 'Otro',
    ];

    const STATUSES = [
        'pending' => 'Pendiente',
        'received' => 'Recibido',
        'verified' => 'Verificado',
        'rejected' => 'Rechazado',
    ];

    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
        ];
    }

    public function rentalProcess() { return $this->belongsTo(RentalProcess::class); }
    public function operation() { return $this->belongsTo(Operation::class); }
    public function client() { return $this->belongsTo(Client::class); }
    public function property() { return $this->belongsTo(Property::class); }
    public function uploader() { return $this->belongsTo(User::class, 'uploaded_by'); }
    public function verifier() { return $this->belongsTo(User::class, 'verified_by'); }

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getFileSizeFormattedAttribute(): string
    {
        if (!$this->file_size) return '';
        if ($this->file_size < 1024) return $this->file_size . ' B';
        if ($this->file_size < 1048576) return round($this->file_size / 1024, 1) . ' KB';
        return round($this->file_size / 1048576, 1) . ' MB';
    }
}
