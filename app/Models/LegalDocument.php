<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class LegalDocument extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'type',
        'is_public',
        'status',
        'current_version_id',
        'meta_description',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
        ];
    }

    public const TYPES = [
        'aviso_privacidad' => 'Aviso de Privacidad',
        'terminos_condiciones' => 'Términos y Condiciones',
        'contrato' => 'Contrato',
        'otro' => 'Otro',
    ];

    // ─── Relations ──────────────────────────────────────

    public function versions(): HasMany
    {
        return $this->hasMany(LegalDocumentVersion::class);
    }

    public function currentVersion(): BelongsTo
    {
        return $this->belongsTo(LegalDocumentVersion::class, 'current_version_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function acceptances(): HasMany
    {
        return $this->hasMany(LegalAcceptance::class);
    }

    // ─── Scopes ─────────────────────────────────────────

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    // ─── Methods ────────────────────────────────────────

    /**
     * Create a new version for this document.
     * Deactivates all previous versions, creates the new one as active,
     * and updates the document's current_version_id.
     */
    public function createNewVersion(string $content, ?string $changeNotes = null, ?int $userId = null): LegalDocumentVersion
    {
        // Deactivate all previous versions
        $this->versions()->update(['is_active' => false]);

        // Determine the next version number
        $nextVersion = ($this->versions()->max('version_number') ?? 0) + 1;

        // Create the new version
        $version = $this->versions()->create([
            'version_number' => $nextVersion,
            'content' => $content,
            'change_notes' => $changeNotes,
            'created_by' => $userId,
            'is_active' => true,
            'published_at' => now(),
        ]);

        // Update the document's current version pointer
        $this->update(['current_version_id' => $version->id]);

        return $version;
    }
}
