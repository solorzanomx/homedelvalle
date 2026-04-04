<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;

class LegalAcceptance extends Model
{
    protected $fillable = [
        'legal_document_id',
        'legal_document_version_id',
        'email',
        'ip_address',
        'user_agent',
        'accepted_at',
        'context',
        'extra_data',
    ];

    protected function casts(): array
    {
        return [
            'accepted_at' => 'datetime',
            'extra_data' => 'array',
        ];
    }

    // ─── Relations ──────────────────────────────────────

    public function document(): BelongsTo
    {
        return $this->belongsTo(LegalDocument::class, 'legal_document_id');
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(LegalDocumentVersion::class, 'legal_document_version_id');
    }

    // ─── Static Methods ─────────────────────────────────

    /**
     * Record a new legal acceptance from the given request.
     */
    public static function record(
        int $documentId,
        int $versionId,
        string $email,
        Request $request,
        string $context = 'web',
        ?array $extraData = null
    ): self {
        return static::create([
            'legal_document_id' => $documentId,
            'legal_document_version_id' => $versionId,
            'email' => $email,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'accepted_at' => now(),
            'context' => $context,
            'extra_data' => $extraData,
        ]);
    }
}
