<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoogleSignatureRequest extends Model
{
    protected $table = 'google_signature_requests';

    protected $fillable = [
        'file_id',
        'signature_request_id',
        'tipo',
        'contacto_id',
        'status',
        'signers',
        'document_name',
        'local_pdf_path',
        'google_response',
        'completed_at',
    ];

    protected $casts = [
        'signers'         => 'array',
        'google_response' => 'array',
        'completed_at'    => 'datetime',
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

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /** URL pública del PDF firmado descargado */
    public function signedPdfUrl(): ?string
    {
        if (!$this->local_pdf_path) return null;
        return url('storage/' . $this->local_pdf_path);
    }

    /** Drive URL para ver el documento en Google Docs */
    public function driveViewUrl(): string
    {
        return "https://drive.google.com/file/d/{$this->file_id}/view";
    }
}
