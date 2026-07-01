<?php

namespace App\Livewire\Admin;

use App\Models\Captacion;
use App\Models\Client;
use App\Models\MarketColonia;
use App\Services\CaptacionIntakeService;
use App\Services\QuickQuoteService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Wizard de 3 pasos: Cliente → Inmueble → Intención y propuesta.
 * SLA objetivo: agente completa en < 3 minutos.
 * Livewire aprobado en CRM admin por Alex (2026-04-29).
 */
class CreateCaptacionFromCall extends Component
{
    use WithFileUploads;

    // ─── Paso actual ─────────────────────────────────────────────────────────
    public int $step = 1;

    // ─── Cliente existente (vía ?client_id= o buscador) ─────────────────────
    #[Url]
    public ?int $client_id = null;
    #[Url]
    public ?int $form_submission_id = null;
    public bool $usingExistingClient = false;
    public string $clientSearch = '';

    // ─── Paso 1 — Cliente ────────────────────────────────────────────────────
    public string $name    = '';
    public string $phone   = '';
    public string $email   = '';
    public string $whatsapp = '';
    public string $rfc     = '';
    public string $client_address = '';
    public string $civil_status   = '';

    // ─── Paso 2 — Inmueble ───────────────────────────────────────────────────
    public string $property_type    = '';
    // Selección de colonia: ID de MarketColonia o 'otra'
    public string $colony_id        = '';
    public string $colony           = '';   // nombre final (auto o manual)
    public string $colony_cp        = '';   // CP auto-llenado
    public bool   $colony_is_custom = false; // true cuando elige "Otra"
    public string $city             = 'CDMX';
    public string $address          = '';
    public string $area             = '';
    public string $age_category     = 'mid';  // new | mid | old
    public int    $year_built       = 0;       // año de construcción (0 = desconocido)
    public string $bedrooms         = '';
    public string $bathrooms        = '';
    public string $parking          = '';
    public string $price_expected   = '';
    public array  $photos           = [];

    // ─── Cotización en vivo (Paso 2) ────────────────────────────────────────
    public ?array $liveQuote = null;

    // ─── Paso 3 — Intención y propuesta ─────────────────────────────────────
    public string $intent         = 'general';
    public string $commission_pct = '5';
    public string $marketing_plan = '';
    public string $notes_from_call = '';
    public string $source         = 'phone_call';

    // ─── Defaults de plan de marketing por intent ────────────────────────────
    private const MARKETING_DEFAULTS = [
        'general' =>
            "• Fotografía profesional y video recorrido del inmueble\n• Ficha editorial boutique con descripción curada\n• Distribución segmentada a nuestra red de contactos activos\n• Difusión en portales seleccionados y redes especializadas\n• Seguimiento semanal con reporte de actividad",
        'venta_constructor' =>
            "• Análisis de potencial de desarrollo y zonificación aplicable\n• Brief técnico con densidades, CUS, COS y restricciones\n• Presentación directa a nuestra red de 30+ desarrolladores activos en BJ\n• Negociación estructurada para maximizar el precio por m²\n• Acompañamiento en due diligence técnico y legal",
        'venta_residencial' =>
            "• Fotografía profesional y video tour inmersivo\n• Ficha editorial con descripción de lifestyle del inmueble\n• Pre-filtro de compradores para mostrar solo a perfiles calificados\n• Acceso al Observatorio de Precios HDV para fijar el precio óptimo\n• Reportes de actividad semanales con métricas reales",
        'venta_comercial' =>
            "• Análisis de rentabilidad y cap rate del inmueble\n• Presentación a red de inversionistas y family offices activos\n• Valoración comparativa con transacciones recientes de la zona\n• Due diligence legal completo antes de la firma\n• Gestión completa hasta escrituración",
        'renta_residencial' =>
            "• Calificación exhaustiva de candidatos (buró, comprobante, referencias)\n• Póliza jurídica que cubre hasta 18 meses de renta en caso de incumplimiento\n• Contrato de arrendamiento con cláusulas de protección al propietario\n• Administración de cobro mensual y reporte de pagos\n• Inspección de entrega y devolución documentada",
        'renta_comercial' =>
            "• Búsqueda segmentada de arrendatario según giro y perfil de riesgo\n• Contrato comercial con ajustes anuales y penalidades claras\n• Garantía corporativa o fianza afianzadora de primer nivel\n• Revisión de uso permitido y restricciones de operación\n• Seguimiento durante toda la vigencia del contrato",
    ];

    public function mount(): void
    {
        $this->marketing_plan = self::MARKETING_DEFAULTS['general'];

        if ($this->client_id) {
            $client = Client::find($this->client_id);
            if ($client) {
                $this->loadExistingClient($client);
            } else {
                $this->client_id = null;
            }
        }
    }

    /**
     * Precarga los datos del Paso 1 desde un cliente ya existente (llegó por
     * ?client_id= desde el kanban / "Convertir a Cliente", o se eligió en el
     * buscador). Evita re-teclear datos y duplicar clientes.
     * Ver docs/07-FLUJO-CAPTACION-Y-MEJORAS.md.
     */
    private function loadExistingClient(Client $client): void
    {
        $this->client_id           = $client->id;
        $this->usingExistingClient = true;
        $this->name                = $client->name ?? '';
        $this->phone               = $client->phone ?? '';
        $this->email               = $client->email ?? '';
        $this->whatsapp            = $client->whatsapp ?? $client->phone ?? '';
        $this->rfc                 = $client->rfc ?? '';
        $this->client_address      = $client->address ?? '';
        $this->clientSearch        = '';
    }

    /** El broker eligió un resultado del buscador de clientes. */
    public function selectExistingClient(int $id): void
    {
        $client = Client::find($id);
        if ($client) {
            $this->loadExistingClient($client);
        }
    }

    /** El broker prefiere capturar los datos manualmente en vez del cliente precargado. */
    public function useDifferentClient(): void
    {
        $this->client_id           = null;
        $this->usingExistingClient = false;
        $this->name = $this->phone = $this->email = $this->whatsapp = $this->rfc = $this->client_address = '';
    }

    /** Resultados del buscador — 5 más relevantes por nombre/teléfono/email. */
    public function getClientMatchesProperty()
    {
        if ($this->usingExistingClient || mb_strlen($this->clientSearch) < 3) {
            return collect();
        }

        $term = $this->clientSearch;

        return Client::where('name', 'like', "%{$term}%")
            ->orWhere('phone', 'like', "%{$term}%")
            ->orWhere('email', 'like', "%{$term}%")
            ->limit(5)
            ->get();
    }

    // ─── Navegación entre pasos ───────────────────────────────────────────────

    public function nextStep(): void
    {
        $this->validate($this->stepRules());
        $this->step++;
    }

    public function prevStep(): void
    {
        if ($this->step > 1) {
            $this->step--;
        }
    }

    // ─── Reaccionar al seleccionar una colonia ────────────────────────────────

    public function updatedColonyId(string $value): void
    {
        if ($value === 'otra' || $value === '') {
            $this->colony_is_custom = true;
            $this->colony           = '';
            $this->colony_cp        = '';
            $this->liveQuote        = null;
            return;
        }

        $this->colony_is_custom = false;

        $colonia = MarketColonia::find((int) $value);
        if ($colonia) {
            $this->colony    = $colonia->name;
            $this->colony_cp = $colonia->cp ?? '';
        }

        $this->refreshLiveQuote();
    }

    // ─── Cotización en vivo — hooks de campos del Paso 2 ─────────────────────

    public function updatedPropertyType(): void { $this->refreshLiveQuote(); }
    public function updatedArea(): void          { $this->refreshLiveQuote(); }
    public function updatedAgeCategory(): void   { $this->refreshLiveQuote(); }
    public function updatedBedrooms(): void      { $this->refreshLiveQuote(); }
    public function updatedBathrooms(): void     { $this->refreshLiveQuote(); }
    public function updatedParking(): void       { $this->refreshLiveQuote(); }

    public function updatedYearBuilt(): void
    {
        if ($this->year_built >= 1900 && $this->year_built <= now()->year) {
            $age = now()->year - $this->year_built;
            $this->age_category = match(true) {
                $age <= 5  => 'new',
                $age <= 20 => 'mid',
                default    => 'old',
            };
        }
        $this->refreshLiveQuote();
    }

    // ─── Actualizar plan de marketing cuando cambia el intent ────────────────

    public function updatedIntent(string $value): void
    {
        $this->marketing_plan = self::MARKETING_DEFAULTS[$value] ?? self::MARKETING_DEFAULTS['general'];
    }

    // ─── Guardar ─────────────────────────────────────────────────────────────

    public function save(bool $goToPresentation = false): void
    {
        $this->validate($this->stepRules());

        /** @var \App\Models\User $agent */
        $agent = Auth::user();

        try {
            $captacion = app(CaptacionIntakeService::class)->createFromCall(
                $this->buildPayload(),
                $agent
            );
        } catch (\Throwable $e) {
            $this->addError('general', 'Error al guardar la captación: ' . $e->getMessage());
            \Illuminate\Support\Facades\Log::error('CreateCaptacionFromCall::save failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return;
        }

        // Adjuntar fotos vía Spatie Media Library
        if (!empty($this->photos)) {
            foreach ($this->photos as $photo) {
                try {
                    $captacion
                        ->addMedia($photo->getRealPath())
                        ->usingFileName($photo->getClientOriginalName())
                        ->toMediaCollection('property_photos');
                } catch (\Throwable $e) {
                    // Las fotos no son bloqueantes — continuar
                    \Illuminate\Support\Facades\Log::warning('Error al adjuntar foto', ['error' => $e->getMessage()]);
                }
            }
        }

        // URL relativa para evitar problemas de host (localhost vs 127.0.0.1)
        $path = $goToPresentation
            ? route('admin.captaciones.presentation', ['captacion' => $captacion->id], false)
            : route('admin.captaciones.show', ['captacion' => $captacion->id], false);

        $this->redirect($path, navigate: false);
    }

    // ─── Render ──────────────────────────────────────────────────────────────

    public function render()
    {
        // Colonias del observatorio agrupadas por zona, ordenadas
        $coloniasByZone = MarketColonia::with('zone')
            ->orderBy('name')
            ->get()
            ->groupBy(fn($c) => $c->zone->name ?? 'Otras');

        return view('livewire.admin.create-captacion-from-call', [
            'propertyTypes' => [
                'House'      => 'Casa',
                'Apartment'  => 'Departamento',
                'Land'       => 'Terreno',
                'Office'     => 'Oficina',
                'Commercial' => 'Local comercial',
                'Warehouse'  => 'Bodega',
                'Building'   => 'Edificio',
            ],
            'intents'        => Captacion::INTENTS,
            'sources'        => Captacion::SOURCES,
            'coloniasByZone' => $coloniasByZone,
        ]);
    }

    // ─── Helpers privados ─────────────────────────────────────────────────────

    private function stepRules(): array
    {
        return match($this->step) {
            1 => [
                'name'  => 'required|string|max:255',
                'phone' => 'required|string|max:30',
                'email' => 'nullable|email|max:255',
            ],
            2 => [
                'property_type' => 'required|string',
                'colony'        => 'required|string|max:255',
                'photos.*'      => 'nullable|image|max:10240',
            ],
            3 => [
                'intent'         => 'required|string',
                'commission_pct' => 'required|numeric|min:0|max:100',
                'marketing_plan' => 'required|string',
            ],
            default => [],
        };
    }

    private function refreshLiveQuote(): void
    {
        // Necesitamos colonia del observatorio, tipo y m² mínimos
        if (
            ! $this->colony_id
            || $this->colony_id === 'otra'
            || ! $this->area
            || (float) $this->area < 10
        ) {
            $this->liveQuote = null;
            return;
        }

        // Mapeo de tipos del formulario → QuickQuoteService
        $typeMap = [
            'House'      => 'house',
            'Apartment'  => 'apartment',
            'Office'     => 'office',
            'Land'       => 'land',
            'Commercial' => 'office',
            'Warehouse'  => 'office',
            'Building'   => 'house',
        ];
        $qqType = $typeMap[$this->property_type] ?? 'apartment';

        // Parking: vacío = no especificado (-1), número = cantidad de cajones
        $qqParking = ($this->parking !== '' && $this->parking !== null)
            ? (int) $this->parking
            : -1;

        // Edad exacta: si se capturó año de construcción lo derivamos; si no, 0 (sin ajuste fino)
        $exactAge = ($this->year_built >= 1900 && $this->year_built <= now()->year)
            ? (now()->year - $this->year_built)
            : 0;

        try {
            $this->liveQuote = app(QuickQuoteService::class)->calculate(
                coloniaId:      (int) $this->colony_id,
                propertyType:   $qqType,
                m2Construction: (float) $this->area,
                ageCategory:    $this->age_category ?: 'mid',
                exactAge:       $exactAge,
                bedrooms:       $this->bedrooms  ? (int) $this->bedrooms  : 0,
                bathrooms:      $this->bathrooms ? (int) $this->bathrooms : 0,
                parking:        $qqParking,
            );
        } catch (\Throwable) {
            $this->liveQuote = null;
        }
    }

    private function buildPayload(): array
    {
        return [
            'client_id'          => $this->client_id,
            'form_submission_id' => $this->form_submission_id,
            'name'           => $this->name,
            'phone'          => $this->phone,
            'email'          => $this->email          ?: null,
            'whatsapp'       => $this->whatsapp        ?: ($this->phone ?: null),
            'rfc'            => $this->rfc             ?: null,
            'client_address' => $this->client_address  ?: null,
            'property_type'  => $this->property_type,
            'colony'         => $this->colony ?: '',
            'colony_cp'      => $this->colony_cp ?: null,
            'city'           => $this->city            ?: 'CDMX',
            'address'        => $this->address         ?: null,
            'area'           => $this->area            ? (float)$this->area    : null,
            'year_built'     => ($this->year_built >= 1900 && $this->year_built <= now()->year) ? $this->year_built : null,
            'bedrooms'       => $this->bedrooms        ? (int)$this->bedrooms  : null,
            'bathrooms'      => $this->bathrooms       ? (int)$this->bathrooms : null,
            'parking'        => $this->parking         ? (int)$this->parking   : null,
            'price_expected' => $this->price_expected  ? (float)str_replace(',', '', $this->price_expected) : null,
            'intent'         => $this->intent,
            'commission_pct' => (float)$this->commission_pct,
            'marketing_plan' => $this->marketing_plan,
            'notes_from_call'=> $this->notes_from_call ?: null,
            'source'         => $this->source,
        ];
    }
}
