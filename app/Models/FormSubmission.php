<?php

namespace App\Models;

use App\Events\FormSubmitted;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class FormSubmission extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'form_type',
        'source_page',
        'full_name',
        'email',
        'phone',
        'payload',
        'lead_tag',
        'status',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'referrer',
        'ip',
        'user_agent',
        'contacted_at',
        'assigned_to',
        'notes',
        // Keep legacy fields for backward compatibility
        'form_id',
        'data',
        'ip_address',
        'is_read',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'data' => 'array',
            'contacted_at' => 'datetime',
            'is_read' => 'boolean',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::created(function (self $submission) {
            $isDuplicate = static::where('email', $submission->email)
                ->where('form_type', $submission->form_type)
                ->where('id', '!=', $submission->id)
                ->where('created_at', '>=', now()->subSeconds(30))
                ->exists();

            \Illuminate\Support\Facades\Log::info('FormSubmission::created fired', [
                'id'           => $submission->id,
                'email'        => $submission->email,
                'form_type'    => $submission->form_type,
                'is_duplicate' => $isDuplicate,
                'trace'        => collect(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 8))
                    ->map(fn($f) => ($f['class'] ?? '') . '::' . ($f['function'] ?? ''))
                    ->implode(' → '),
            ]);

            if (! $isDuplicate) {
                FormSubmitted::dispatch($submission);
            }
        });
    }

    /**
     * Register Spatie Media Library collections
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('briefs')
            ->acceptsMimeTypes([
                'application/pdf',
                'image/jpeg',
                'image/png',
            ])
            ->singleFile();
    }

    /**
     * Get the user this submission is assigned to
     */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the form (legacy relationship)
     */
    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    /**
     * Get human-readable form type label
     */
    public function getFormTypeLabel(): string
    {
        return match ($this->form_type) {
            'vendedor' => 'Vendedor/Valuación',
            'comprador' => 'Comprador/Búsqueda',
            'b2b' => 'Desarrollador/Inversionista',
            'contacto' => 'Contacto General',
            'propiedad' => 'Consulta de Propiedad',
            default => ucfirst($this->form_type ?? ''),
        };
    }

    /**
     * Get human-readable status label
     */
    public function getStatusLabel(): string
    {
        return match ($this->status) {
            'new' => 'Nuevo',
            'contacted' => 'Contactado',
            'qualified' => 'Calificado',
            'won' => 'Ganado',
            'lost' => 'Perdido',
            default => ucfirst($this->status ?? ''),
        };
    }

    /**
     * Scope to filter by form type
     */
    public function scopeByFormType($query, string $formType)
    {
        return $query->where('form_type', $formType);
    }

    /**
     * Scope to filter by status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by lead tag
     */
    public function scopeByLeadTag($query, string $tag)
    {
        return $query->where('lead_tag', $tag);
    }

    /**
     * Scope to filter uncontacted submissions
     */
    public function scopeUncontacted($query)
    {
        return $query->where('status', 'new');
    }

    /**
     * Scope to filter recent submissions (last 24 hours)
     */
    public function scopeRecent($query)
    {
        return $query->where('created_at', '>=', now()->subDay());
    }
}
