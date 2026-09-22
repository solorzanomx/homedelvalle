<?php

namespace App\Models;

use App\Observers\ClientObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy(ClientObserver::class)]
class Client extends Model
{
    const CLIENT_TYPES = [
        'owner' => 'Propietario',
        'buyer' => 'Comprador',
        'investor' => 'Inversionista',
        'renter' => 'Inquilino',
    ];

    protected $fillable = [
        'name', 'email', 'phone', 'whatsapp',
        'address', 'city', 'curp', 'rfc',
        'interest_types', 'lead_temperature', 'priority', 'initial_notes',
        'budget_min', 'budget_max', 'property_type', 'search_urgency',
        'broker_id', 'assigned_user_id',
        'photo', 'user_id',
        'marketing_channel_id', 'marketing_campaign_id',
        'acquisition_cost', 'utm_source', 'utm_medium', 'utm_campaign',
        'client_type', 'lead_source',
        // Datos legales — nombre desglosado
        'first_name', 'last_name_paterno', 'last_name_materno',
        // Datos personales
        'birth_date', 'birth_state', 'gender', 'nationality', 'marital_status', 'occupation',
        // Identificación oficial
        'id_type', 'id_number', 'id_expiry',
        // Domicilio estructurado
        'address_street', 'address_colony', 'address_municipality', 'address_state', 'address_zip',
        // Datos para contratos de renta
        'marital_regime', 'spouse_name', 'spouse_curp', 'bank_clabe', 'bank_name',
        // Ingresos (arrendatario/comprador)
        'income_type', 'income_amount',
        // Financiamiento (comprador)
        'financing_type', 'financing_preauth_amount', 'nss', 'infonavit_balance',
        // Cuestionario de arrendamiento — datos que no se cubrían con lo de arriba
        // (ver App\Support\TenantDocumentChecklist, basado en el cuestionario en
        // papel "Persona Física")
        'occupants_count',
        'employer_name', 'employer_address', 'employer_phone', 'job_seniority',
        'other_income_amount', 'other_income_description',
        'previous_landlord_name', 'previous_landlord_phone', 'previous_landlord_mobile',
        'previous_landlord_email', 'previous_landlord_years',
        // Vigencia de identificación (mes/año, como viene en el INE)
        'id_expiry_month', 'id_expiry_year',
        // Cómo comprueba ingresos + mascotas
        'income_proof_type', 'pets',
        // Qué comprobante de domicilio va a subir
        'domicilio_proof_type',
    ];

    const DOMICILIO_PROOF_TYPES = [
        'agua' => 'Recibo de Agua',
        'luz'  => 'Recibo de Luz (CFE)',
        'gas'  => 'Recibo de Gas',
    ];

    const INCOME_PROOF_TYPES = [
        'nomina'                => 'Recibos de Nómina',
        'estado_cuenta'         => 'Estados de Cuenta Bancarios',
        'declaracion_impuestos' => 'Declaración de Impuestos',
        'cfdi_honorarios'       => 'CFDI de Honorarios',
        'otro'                  => 'Otro comprobante',
    ];

    // category de Document que corresponde a cada income_proof_type
    const INCOME_PROOF_CATEGORY = [
        'nomina'                => 'nomina',
        'estado_cuenta'         => 'estado_cuenta',
        'declaracion_impuestos' => 'proof_of_income',
        'cfdi_honorarios'       => 'cfdi_honorarios',
        'otro'                  => 'proof_of_income',
    ];

    const PET_TYPES = ['perro' => 'Perro', 'gato' => 'Gato'];
    const PET_SIZES = ['chico' => 'Chico', 'mediano' => 'Mediano', 'grande' => 'Grande'];

    protected $casts = [
        'budget_min'   => 'decimal:2',
        'budget_max'   => 'decimal:2',
        'acquisition_cost' => 'decimal:2',
        'interest_types'   => 'array',
        'client_type'  => 'string',
        'birth_date'   => 'date',
        'id_expiry'    => 'date',
        'curp_verified_at'        => 'datetime',
        'rfc_verified_at'         => 'datetime',
        'income_amount'           => 'decimal:2',
        'financing_preauth_amount'=> 'decimal:2',
        'infonavit_balance'       => 'decimal:2',
        'other_income_amount'     => 'decimal:2',
        'pets'                    => 'array',
    ];

    /**
     * Deriva client_type (enum owner|buyer|investor|renter) a partir de
     * interest_types (compra|venta|renta_propietario|renta_inquilino) —
     * único algoritmo correcto en el sistema, antes solo vivía inline en
     * ClientController::store(); varios otros puntos de alta/edición de
     * Client (webhook, AutomationEngine, conversión de FormSubmission,
     * CreateOperationFromLead) lo omitían o usaban valores fuera del enum
     * real (bug real encontrado en la auditoría 2026-07-04). is_investor
     * pisa todo lo demás.
     */
    public static function deriveClientType(array $interestTypes, bool $isInvestor = false): ?string
    {
        if ($isInvestor) {
            return 'investor';
        }
        if (in_array('venta', $interestTypes) || in_array('renta_propietario', $interestTypes)) {
            return 'owner';
        }
        if (in_array('compra', $interestTypes)) {
            return 'buyer';
        }
        if (in_array('renta_inquilino', $interestTypes)) {
            return 'renter';
        }
        return null;
    }

    public function broker(): BelongsTo
    {
        return $this->belongsTo(Broker::class);
    }

    public function portalUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function marketingChannel(): BelongsTo
    {
        return $this->belongsTo(MarketingChannel::class);
    }

    public function marketingCampaign(): BelongsTo
    {
        return $this->belongsTo(MarketingCampaign::class);
    }

    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class);
    }

    public function emails(): HasMany
    {
        return $this->hasMany(ClientEmail::class);
    }

    public function interactions(): HasMany
    {
        return $this->hasMany(Interaction::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function references(): HasMany
    {
        return $this->hasMany(ClientReference::class)->orderBy('sort_order');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function operations(): HasMany
    {
        return $this->hasMany(Operation::class);
    }

    public function ownedProperties(): HasMany
    {
        return $this->hasMany(Property::class, 'client_id');
    }

    public function avales(): HasMany
    {
        return $this->hasMany(\App\Models\RentalAval::class);
    }

    // ── Marketing Automation ──────────────────────────
    public function segments(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Segment::class)->withPivot('entered_at', 'exited_at');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function leadEvents(): HasMany
    {
        return $this->hasMany(LeadEvent::class);
    }

    public function leadScore(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(LeadScore::class);
    }

    public function automationEnrollments(): HasMany
    {
        return $this->hasMany(AutomationEnrollment::class);
    }

    // ── Accessors de nombre legal ─────────────────────────────

    /** Nombre completo para contratos: APELLIDO PATERNO APELLIDO MATERNO NOMBRE(S) */
    public function getFullNameLegalAttribute(): string
    {
        if ($this->first_name && $this->last_name_paterno) {
            return strtoupper(trim(
                ($this->last_name_paterno ?? '') . ' ' .
                ($this->last_name_materno ?? '') . ' ' .
                ($this->first_name ?? '')
            ));
        }
        return strtoupper($this->name ?? '');
    }

    /** Nombre completo natural: Nombre(s) Apellido Paterno Apellido Materno */
    public function getFullNameAttribute(): string
    {
        if ($this->first_name && $this->last_name_paterno) {
            return trim(
                ($this->first_name ?? '') . ' ' .
                ($this->last_name_paterno ?? '') . ' ' .
                ($this->last_name_materno ?? '')
            );
        }
        return $this->name ?? '';
    }

    /** Calcula % de completitud de ficha legal (0-100) */
    public function getLegalCompletenessAttribute(): int
    {
        $fields = [
            'first_name', 'last_name_paterno', 'last_name_materno',
            'birth_date', 'birth_state', 'gender', 'nationality', 'marital_status',
            'curp', 'rfc',
            'id_type', 'id_number', 'id_expiry_month', 'id_expiry_year',
            'address_street', 'address_colony', 'address_municipality', 'address_state', 'address_zip',
        ];

        $interests = $this->interest_types ?? [];
        if (in_array('renta_inquilino', $interests)) {
            $fields = array_merge($fields, ['occupants_count', 'income_proof_type', 'employer_name', 'previous_landlord_name']);
        }
        if (in_array('compra', $interests)) {
            $fields[] = 'financing_type';
        }

        $filled = collect($fields)->filter(fn($f) => !empty($this->$f))->count();
        return (int) round(($filled / count($fields)) * 100);
    }

    /** Domicilio estructurado como string para contratos */
    public function getLegalAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address_street,
            $this->address_colony ? 'Col. ' . $this->address_colony : null,
            $this->address_municipality,
            $this->address_state,
            $this->address_zip ? 'C.P. ' . $this->address_zip : null,
        ]);
        return implode(', ', $parts) ?: ($this->address ?? '');
    }

    public function getScoreAttribute(): int
    {
        return $this->leadScore?->total_score ?? 0;
    }

    public function getGradeAttribute(): string
    {
        return $this->leadScore?->grade ?? 'D';
    }
}
