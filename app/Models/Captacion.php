<?php

namespace App\Models;

use App\Observers\CaptacionObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[ObservedBy(CaptacionObserver::class)]
class Captacion extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $table = 'captaciones';

    protected $fillable = [
        'client_id', 'property_id', 'operation_id', 'property_address', 'portal_etapa',
        'motivo', 'urgencia', 'situacion_herencia',
        'etapa1_completed_at', 'etapa2_completed_at', 'etapa3_completed_at', 'etapa4_completed_at',
        'etapa3_valuation_id', 'etapa4_signature_id', 'precio_acordado', 'status',
        'intent', 'commission_pct', 'marketing_plan', 'notes_from_call', 'source',
        'created_by_user_id', 'declined_at', 'declined_reason',
        'last_presentation_pdf_path',
        'last_servicios_pdf_path',
    ];

    protected function casts(): array
    {
        return [
            'etapa1_completed_at' => 'datetime',
            'etapa2_completed_at' => 'datetime',
            'etapa3_completed_at' => 'datetime',
            'etapa4_completed_at' => 'datetime',
            'precio_acordado'     => 'decimal:2',
            'commission_pct'      => 'decimal:2',
            'declined_at'         => 'datetime',
        ];
    }

    const INTENTS = [
        'general'            => 'General',
        'venta_constructor'  => 'Venta a constructor',
        'venta_residencial'  => 'Venta residencial',
        'venta_comercial'    => 'Venta comercial',
        'renta_residencial'  => 'Renta residencial',
        'renta_comercial'    => 'Renta comercial',
    ];

    const SOURCES = [
        'phone_call'        => 'Llamada telefónica',
        'whatsapp_inbound'  => 'WhatsApp entrante',
        'web_form'          => 'Formulario web',
        'referral'          => 'Referido',
        'other'             => 'Otro',
    ];

    // Documentos requeridos para avanzar de etapa 1 (categorías)
    const REQUIRED_DOCS_ETAPA1 = ['identificacion', 'curp', 'comprobante_domicilio'];

    // Universo completo de documentos opcionales posibles (no todos aplican a
    // toda captación — ver getApplicableOptionalDocs() para cuáles aplican a
    // una captación específica según estado civil/situación de herencia).
    const OPTIONAL_DOCS_ETAPA1 = ['acta_matrimonio', 'testamento', 'declaratoria_herederos'];

    const SITUACION_HERENCIA_LABELS = [
        'no_aplica'       => 'No aplica',
        'con_testamento'  => 'Con testamento',
        'intestado'       => 'Intestado (Declaratoria de Herederos)',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function operation()
    {
        return $this->belongsTo(Operation::class);
    }

    public function sends()
    {
        return $this->hasMany(PresentationSend::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function valuation()
    {
        return $this->belongsTo(PropertyValuation::class, 'etapa3_valuation_id');
    }

    public function signatureRequest()
    {
        return $this->belongsTo(GoogleSignatureRequest::class, 'etapa4_signature_id');
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    // ─── Stage helpers ────────────────────────────────────────────────────────

    /**
     * Etapa 1 complete when all required doc categories are approved.
     */
    public function isEtapa1Complete(): bool
    {
        $approvedCategories = $this->documents()
            ->where('captacion_status', 'aprobado')
            ->pluck('category')
            ->toArray();

        foreach (self::REQUIRED_DOCS_ETAPA1 as $cat) {
            if (!in_array($cat, $approvedCategories)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Documentos opcionales que aplican REALMENTE a esta captación — no todo
     * el universo de OPTIONAL_DOCS_ETAPA1 aplica siempre (acta de matrimonio
     * solo si el propietario está casado, testamento/declaratoria solo si el
     * inmueble se adquirió por herencia).
     */
    public function getApplicableOptionalDocs(): array
    {
        $docs = [];
        if ($this->client?->marital_status === 'casado') {
            $docs[] = 'acta_matrimonio';
        }
        if ($this->situacion_herencia === 'con_testamento') {
            $docs[] = 'testamento';
        } elseif ($this->situacion_herencia === 'intestado') {
            $docs[] = 'declaratoria_herederos';
        }
        return $docs;
    }

    /**
     * Etapa 2 complete when valuation is linked.
     */
    public function isEtapa2Complete(): bool
    {
        return !is_null($this->etapa3_valuation_id);
    }

    /**
     * Etapa 3 complete when price is agreed.
     */
    public function isEtapa3Complete(): bool
    {
        return !is_null($this->precio_acordado);
    }

    /**
     * Etapa 4 complete when exclusiva contract is signed.
     */
    public function isEtapa4Complete(): bool
    {
        return $this->signatureRequest?->status === 'completed';
    }

    public function getCurrentEtapa(): int
    {
        if ($this->isEtapa4Complete()) return 4;
        if ($this->isEtapa3Complete()) return 4; // waiting on signature
        if ($this->isEtapa2Complete()) return 3;
        if ($this->isEtapa1Complete()) return 2;
        return 1;
    }

    public function getPendingRequiredDocs(): array
    {
        $approvedCategories = $this->documents()
            ->where('captacion_status', 'aprobado')
            ->pluck('category')
            ->toArray();

        return array_filter(self::REQUIRED_DOCS_ETAPA1, fn($cat) => !in_array($cat, $approvedCategories));
    }

    // ─── Media Library ───────────────────────────────────────────────────────

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('property_photos')
             ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
    }

    // ─── Presentation helpers ─────────────────────────────────────────────────

    public function getIntentLabelAttribute(): string
    {
        return self::INTENTS[$this->intent ?? 'general'] ?? 'General';
    }

    public function getSourceLabelAttribute(): string
    {
        return self::SOURCES[$this->source ?? 'phone_call'] ?? 'Llamada telefónica';
    }

    /** Minutos desde created_at hasta el primer envío de presentación. */
    public function timeToFirstSend(): ?int
    {
        $first = $this->sends()->orderBy('sent_at')->first();
        return $first ? (int) $this->created_at->diffInMinutes($first->sent_at) : null;
    }

    public function isDeclined(): bool
    {
        return $this->status === 'declinado' || !is_null($this->declined_at);
    }

    public function hasPresentationSent(): bool
    {
        return $this->sends()->whereIn('channel', ['email', 'whatsapp'])->exists();
    }

    /** Dirección legible: prioriza la propiedad vinculada, luego property_address legacy. */
    public function getPropertyAddressDisplayAttribute(): string
    {
        if ($this->property && $this->property->colony) {
            return trim($this->property->title . ' · ' . $this->property->colony);
        }
        return $this->property_address ?? '—';
    }
}
