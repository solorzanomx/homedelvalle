<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Captacion extends Model
{
    protected $table = 'captaciones';

    protected $fillable = [
        'client_id', 'property_address', 'portal_etapa',
        'etapa1_completed_at', 'etapa2_completed_at', 'etapa3_completed_at', 'etapa4_completed_at',
        'etapa3_valuation_id', 'etapa4_signature_id', 'precio_acordado', 'status',
    ];

    protected function casts(): array
    {
        return [
            'etapa1_completed_at' => 'datetime',
            'etapa2_completed_at' => 'datetime',
            'etapa3_completed_at' => 'datetime',
            'etapa4_completed_at' => 'datetime',
            'precio_acordado' => 'decimal:2',
        ];
    }

    // Documentos requeridos para avanzar de etapa 1 (categorías)
    const REQUIRED_DOCS_ETAPA1 = ['identificacion', 'curp', 'comprobante_domicilio'];

    // Documentos opcionales que se muestran pero no bloquean
    const OPTIONAL_DOCS_ETAPA1 = ['acta_matrimonio', 'testamento'];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function client()
    {
        return $this->belongsTo(Client::class);
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
}
