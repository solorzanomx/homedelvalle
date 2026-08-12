<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractVersion extends Model
{
    protected $fillable = [
        'contract_id', 'version_number', 'generated_html', 'pdf_path', 'clauses_snapshot',
        'generated_by', 'generation_note', 'signature_status', 'signature_data',
        'signed_at', 'signed_by', 'sent_at',
    ];

    const SIGNATURE_STATUSES = [
        'unsigned' => 'Sin Firmar',
        'pending_signature' => 'Pendiente de Firma',
        'signed' => 'Firmado',
    ];

    protected function casts(): array
    {
        return [
            'clauses_snapshot' => 'array',
            'signature_data' => 'array',
            'signed_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function generator()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function signer()
    {
        return $this->belongsTo(User::class, 'signed_by');
    }

    public function getIsSignedAttribute(): bool
    {
        return $this->signature_status === 'signed';
    }

    public function getSignatureStatusLabelAttribute(): string
    {
        return self::SIGNATURE_STATUSES[$this->signature_status] ?? ucfirst($this->signature_status);
    }
}
