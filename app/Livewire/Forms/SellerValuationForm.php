<?php

namespace App\Livewire\Forms;

use App\Models\FormSubmission;
use App\Models\Client;
use App\Models\LegalAcceptance;
use App\Models\LegalDocument;
use App\Models\Operation;
use App\Services\AutomationEngine;
use App\Services\SpamProtectionService;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class SellerValuationForm extends Component
{
    // Honeypot — un humano nunca lo llena (oculto por CSS, no type=hidden).
    public string $website_url = '';

    public string $nombre = '';
    public string $email = '';
    public string $whatsapp = '';
    public string $tipo_propiedad = '';
    public string $colonia = '';
    public ?int $superficie_m2 = null;
    public string $recamaras = '';
    public string $precio_esperado = '';
    public string $motivo = '';
    public string $estado_doc = '';
    public string $timing = '';
    public bool $aviso = false;

    public bool $submitted = false;
    public bool $isProcessing = false;
    public string $folio = '';
    public string $clientName = '';

    /**
     * Piloto multi-paso (2026-07-17): 1 Propiedad → 2 Situación → 3 Contacto.
     * Datos personales al final — el visitante invierte antes de exponerse.
     * Cada avance valida solo sus campos y emite 'form-step' para medir el
     * embudo en GA4 (form_start → form_step 2 → 3 → generate_lead).
     * Si el piloto no mejora la tasa de finalización, se revierte el commit.
     */
    public int $step = 1;

    private const STEP_FIELDS = [
        1 => ['tipo_propiedad', 'colonia', 'superficie_m2', 'recamaras'],
        2 => ['precio_esperado', 'motivo', 'estado_doc', 'timing'],
    ];

    public function nextStep(): void
    {
        $this->validate(
            collect($this->rules())->only(self::STEP_FIELDS[$this->step] ?? [])->all()
        );

        if ($this->step < 3) {
            $this->step++;
            $this->dispatch('form-step', formType: 'vendedor', step: $this->step);
        }
    }

    public function prevStep(): void
    {
        if ($this->step > 1) {
            $this->step--;
        }
    }

    protected function rules(): array
    {
        return [
            'nombre' => 'required|string|max:120',
            'email' => 'required|email',
            'whatsapp' => ['required', 'regex:/^(\+?52)?\s?[0-9]{10}$/'],
            'tipo_propiedad' => 'required|in:departamento,casa,terreno,oficina,comercial',
            'colonia' => 'required|string|max:160',
            'superficie_m2' => 'nullable|integer|min:1',
            'recamaras' => 'nullable|in:1,2,3,4+,na',
            'precio_esperado' => 'required|in:hasta_4m,4m_6m,6m_9m,9m_14m,14m_plus,no_se',
            'motivo' => 'required|in:mudanza,sucesion,liquidez,patrimonio,otro',
            'estado_doc' => 'required|in:al_corriente,pendientes,sucesion,no_se',
            'timing' => 'required|in:inmediato,1_3m,3_6m,sin_prisa',
            'aviso' => 'accepted',
        ];
    }

    protected array $validationAttributes = [
        'nombre' => 'nombre completo',
        'email' => 'email',
        'whatsapp' => 'WhatsApp',
        'tipo_propiedad' => 'tipo de propiedad',
        'colonia' => 'colonia o dirección',
        'superficie_m2' => 'superficie',
        'recamaras' => 'recámaras',
        'precio_esperado' => 'precio esperado',
        'motivo' => 'motivo de la venta',
        'estado_doc' => 'estado documental',
        'timing' => 'timing',
        'aviso' => 'aviso de privacidad',
    ];

    /**
     * Punto de entrada del <form>: Enter en pasos 1-2 avanza (validando solo
     * ese paso) en vez de disparar la validación completa con errores de
     * campos que el usuario aún no ve.
     */
    public function submitOrNext(SpamProtectionService $spam, AutomationEngine $engine): void
    {
        if ($this->step < 3) {
            $this->nextStep();

            return;
        }

        $this->submit($spam, $engine);
    }

    public function submit(SpamProtectionService $spam, AutomationEngine $engine): void
    {
        $data = $this->validate(); // valida primero — si falla, isProcessing nunca se bloquea
        if ($this->isProcessing) return;
        $this->isProcessing = true;

        // Honeypot: un bot que lo llena recibe la misma pantalla de éxito
        // sin que nada se guarde (mismo patrón que LandingController::submit()).
        if ($this->website_url !== '') {
            $this->reset();
            $this->submitted = true;
            return;
        }

        $spamCheck = $spam->check($data, null, request()->ip(), 'vendedor');
        if (! $spamCheck['pass']) {
            $this->reset();
            $this->submitted = true;
            return;
        }

        $lockKey = 'form_submit_vendedor_' . md5($data['email']);
        if (! Cache::lock($lockKey, 30)->get()) return;

        // Crear FormSubmission (Lead) directamente - NO crear Client ni Operation
        $submission = FormSubmission::create([
            'form_type'   => 'vendedor',
            'source_page' => '/vende-tu-propiedad',
            'full_name'   => $data['nombre'],
            'email'       => $data['email'],
            'phone'       => $data['whatsapp'],
            'payload'     => collect($data)->except(['nombre', 'email', 'whatsapp', 'aviso'])->toArray(),
            'lead_tag'    => 'LEAD_VENDEDOR',
            'client_type' => 'owner',
            'lead_temperature' => $this->calculateLeadTemperature($data),
            'property_type' => $data['tipo_propiedad'],
            'utm_source'  => request()->query('utm_source'),
            'utm_medium'  => request()->query('utm_medium'),
            'utm_campaign'=> request()->query('utm_campaign'),
            'referrer'    => request()->headers->get('referer'),
            'ip'          => request()->ip(),
            'user_agent'  => request()->userAgent(),
        ]);

        $engine->processFormSubmitted([
            'name' => $data['nombre'],
            'email' => $data['email'],
            'phone' => $data['whatsapp'],
            'utm_source' => request()->query('utm_source'),
            'utm_medium' => request()->query('utm_medium'),
            'utm_campaign' => request()->query('utm_campaign'),
            'interest_types' => ['venta'],
        ], 'vendedor', notifyAdmins: false);

        $privacyDoc = LegalDocument::where('type', 'aviso_privacidad')->where('status', 'published')->first();
        if ($privacyDoc && $privacyDoc->current_version_id) {
            LegalAcceptance::record(
                $privacyDoc->id,
                $privacyDoc->current_version_id,
                $data['email'],
                request(),
                'vendedor',
                ['name' => $data['nombre']]
            );
        }

        $savedName  = $data['nombre'];
        $savedFolio = 'HDV-' . strtoupper(substr(md5($submission->id . 'vendedor'), 0, 4)) . '-' . $submission->id;

        $this->reset();
        $this->submitted  = true;
        $this->folio      = $savedFolio;
        $this->clientName = $savedName;
        $this->dispatch('lead-conversion', formType: 'vendedor');
    }

    /**
     * Calcula lead temperature basado en timing y estado documental
     */
    private function calculateLeadTemperature(array $data): string
    {
        $isImmediate = $data['timing'] === 'inmediato';
        $isDocReady = $data['estado_doc'] === 'al_corriente';

        if ($isImmediate && $isDocReady) {
            return 'hot';
        }

        if ($isImmediate || $isDocReady) {
            return 'warm';
        }

        return 'warm';
    }
    // Limpia el error del campo en cuanto el usuario lo corrige
    public function updated(string $propertyName): void
    {
        if ($this->getErrorBag()->has($propertyName)) {
            $this->validateOnly($propertyName);
        }
    }


    public function render()
    {
        return view('livewire.forms.seller-valuation-form');
    }
}

