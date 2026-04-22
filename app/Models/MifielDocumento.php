<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MifielDocumento extends Model
{
    protected $table = 'mifiel_documentos';

    protected $fillable = [
        'document_id',
        'tipo',
        'contacto_id',
        'status',
        'pdf_path',
        'mifiel_response',
        'signed_at',
    ];

    protected $casts = [
        'mifiel_response' => 'array',
        'signed_at'       => 'datetime',
    ];

    // ── Relaciones ────────────────────────────────────────────────────────

    public function contacto(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'contacto_id');
    }

    // ── Scopes ────────────────────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeSigned($query)
    {
        return $query->where('status', 'signed');
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    public function isSigned(): bool
    {
        return $this->status === 'signed';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /** URL pública del PDF firmado (si está guardado en storage/app/public/) */
    public function signedPdfUrl(): ?string
    {
        if (!$this->pdf_path) return null;
        return url('storage/' . $this->pdf_path);
    }
}
