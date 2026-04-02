<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    protected $fillable = ['rental_process_id', 'operation_id', 'contract_template_id', 'type', 'title', 'generated_html', 'pdf_path', 'source', 'signature_status', 'signature_data', 'signed_at', 'signed_by', 'notes',];
    const SIGNATURE_STATUSES = [
        'unsigned' => 'Sin Firmar',
        'pending_signature' => 'Pendiente de Firma',
        'signed' => 'Firmado',
    ];

    const SOURCES = [
        'generated' => 'Generado',
        'uploaded' => 'Subido',
    ];

    protected function casts(): array
    {
        return [
            'signature_data' => 'array',
            'signed_at' => 'datetime',
        ];
    }

    public function rentalProcess() { return $this->belongsTo(RentalProcess::class); }
    public function operation() { return $this->belongsTo(Operation::class); }
    public function template() { return $this->belongsTo(ContractTemplate::class, 'contract_template_id'); }
    public function signer() { return $this->belongsTo(User::class, 'signed_by'); }

    public function getSignatureStatusLabelAttribute(): string
    {
        return self::SIGNATURE_STATUSES[$this->signature_status] ?? ucfirst($this->signature_status);
    }

    public function getIsSignedAttribute(): bool
    {
        return $this->signature_status === 'signed';
    }
}
