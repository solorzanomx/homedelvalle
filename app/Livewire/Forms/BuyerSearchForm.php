<?php

namespace App\Livewire\Forms;

use App\Models\FormSubmission;
use App\Models\Client;
use App\Models\LegalAcceptance;
use App\Models\LegalDocument;
use App\Helpers\BudgetHelper;
use App\Services\AutomationEngine;
use App\Services\SpamProtectionService;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Livewire\WithFileUploads;

class BuyerSearchForm extends Component
{
    use WithFileUploads;

    // Honeypot — un humano nunca lo llena (oculto por CSS, no type=hidden).
    public string $website_url = '';

    // State
    public array $tipo_inmueble = [];
    public string $operacion = 'compra';
    public array $zonas = [];
    public string $recamaras = '';
    public string $presupuesto = '';
    public string $pago = '';
    public string $timing = '';
    public string $must_have = '';
    public string $nombre = '';
    public string $email = '';
    public string $whatsapp = '';
    public bool $aviso = false;

    public bool $submitted = false;
    public bool $isProcessing = false;
    public string $folio = '';
    public string $clientName = '';

    protected function rules(): array
    {
        return [
            'tipo_inmueble' => 'required|array|min:1',
            'tipo_inmueble.*' => 'in:departamento,casa,terreno,oficina,comercial',
            // Fija en 'compra' (default de la propiedad) — el radio se quitó
            // del formulario: /comprar es específico de compra, renta tiene
            // su propio form en /rentar.
            'operacion' => 'required|in:compra',
            'zonas' => 'required|array|min:1',
            'recamaras' => 'required|in:1,2,3,4+',
            'presupuesto' => 'required|in:hasta_4m,4m_6m,6m_9m,9m_14m,14m_plus',
            'pago' => 'required|in:contado,credito,infonavit,fovissste,mixto',
            'timing' => 'required|in:inmediato,1_3m,3_6m,explorando',
            'must_have' => 'nullable|string|max:280',
            'nombre' => 'required|string|max:120',
            'email' => 'required|email',
            'whatsapp' => ['required', 'regex:/^(\+?52)?\s?[0-9]{10}$/'],
            'aviso' => 'accepted',
        ];
    }

    protected array $validationAttributes = [
        'tipo_inmueble' => 'tipo de inmueble',
        'operacion' => 'operación',
        'zonas' => 'zonas de interés',
        'recamaras' => 'recámaras mínimas',
        'presupuesto' => 'presupuesto',
        'pago' => 'forma de pago',
        'timing' => 'timing',
        'must_have' => 'especificaciones',
        'nombre' => 'nombre completo',
        'email' => 'email',
        'whatsapp' => 'WhatsApp',
        'aviso' => 'aviso de privacidad',
    ];

    public function submit(SpamProtectionService $spam, AutomationEngine $engine): void
    {
        $data = $this->validate(); // valida primero — si falla, isProcessing nunca se bloquea
        if ($this->isProcessing) return;
        $this->isProcessing = true;

        if ($this->website_url !== '') {
            $this->reset();
            $this->submitted = true;
            return;
        }

        $spamCheck = $spam->check($data, null, request()->ip(), 'comprador');
        if (! $spamCheck['pass']) {
            $this->reset();
            $this->submitted = true;
            return;
        }

        $lockKey = 'form_submit_comprador_' . md5($data['email']);
        if (! Cache::lock($lockKey, 30)->get()) return;

        // Convertir presupuesto a min/max
        [$budgetMin, $budgetMax] = BudgetHelper::convertRangeToMinMax($data['presupuesto']);

        // Crear FormSubmission (Lead) directamente - NO crear Client
        $submission = FormSubmission::create([
            'form_type'   => 'comprador',
            'source_page' => '/comprar',
            'full_name'   => $data['nombre'],
            'email'       => $data['email'],
            'phone'       => $data['whatsapp'],
            'payload'     => collect($data)->except(['nombre', 'email', 'whatsapp', 'aviso'])->toArray(),
            'lead_tag'    => 'LEAD_COMPRADOR',
            'client_type' => 'buyer',
            'lead_temperature' => $this->calculateLeadTemperature($data),
            'budget_min'  => $budgetMin,
            'budget_max'  => $budgetMax,
            'property_type' => implode(',', $data['tipo_inmueble']),
            // interest_types son intenciones (compra/venta/renta_*), no tipos
            // de inmueble — guardar aquí el tipo_inmueble rompía la derivación
            // de client_type al convertir el lead a Client.
            'interest_types' => ['compra'],
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
            'interest_types' => ['compra'],
        ], 'comprador');

        $privacyDoc = LegalDocument::where('type', 'aviso_privacidad')->where('status', 'published')->first();
        if ($privacyDoc && $privacyDoc->current_version_id) {
            LegalAcceptance::record(
                $privacyDoc->id,
                $privacyDoc->current_version_id,
                $data['email'],
                request(),
                'comprador',
                ['name' => $data['nombre']]
            );
        }

        $savedName  = $data['nombre'];
        $savedFolio = 'HDV-' . strtoupper(substr(md5($submission->id . 'comprador'), 0, 4)) . '-' . $submission->id;

        $this->reset();
        $this->submitted  = true;
        $this->folio      = $savedFolio;
        $this->clientName = $savedName;
    }

    /**
     * Calcula lead temperature basado en timing y presupuesto
     */
    private function calculateLeadTemperature(array $data): string
    {
        $isImmediate = $data['timing'] === 'inmediato';
        $isBudgetHigh = in_array($data['presupuesto'], ['9m_14m', '14m_plus']);

        if ($isImmediate && $isBudgetHigh) {
            return 'hot';
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
        return view('livewire.forms.buyer-search-form');
    }
}

