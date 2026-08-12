<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class Contract extends Model
{
    protected $fillable = ['rental_process_id', 'operation_id', 'contract_template_id', 'current_version_id', 'final_version_id', 'type', 'title', 'folio', 'generated_html', 'pdf_path', 'source', 'signature_status', 'signature_data', 'signed_at', 'signed_by', 'notes',];
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

    public function clauses()
    {
        return $this->morphMany(ContractClause::class, 'clauseable')->orderBy('sort_order');
    }

    public function versions()
    {
        return $this->hasMany(ContractVersion::class)->orderBy('version_number');
    }

    public function currentVersion()
    {
        return $this->belongsTo(ContractVersion::class, 'current_version_id');
    }

    public function finalVersion()
    {
        return $this->belongsTo(ContractVersion::class, 'final_version_id');
    }

    public function nextVersionNumber(): int
    {
        return ($this->versions()->max('version_number') ?? 0) + 1;
    }

    public function getSignatureStatusLabelAttribute(): string
    {
        if ($this->currentVersion) {
            return $this->currentVersion->signature_status_label;
        }

        return self::SIGNATURE_STATUSES[$this->signature_status] ?? ucfirst($this->signature_status);
    }

    public function getIsSignedAttribute(): bool
    {
        if ($this->final_version_id) {
            return true;
        }

        // Fallback legacy: contratos de renta que nunca adoptaron versiones.
        return $this->versions()->doesntExist() && $this->signature_status === 'signed';
    }

    protected static function booted(): void
    {
        static::creating(function (Contract $contract) {
            $hasRental = !is_null($contract->rental_process_id);
            $hasOperation = !is_null($contract->operation_id);

            if ($hasRental === $hasOperation) {
                throw ValidationException::withMessages([
                    'contract' => 'Un contrato debe pertenecer exactamente a una Renta o a una Operación, no a ambas ni a ninguna.',
                ]);
            }
        });
    }
}
