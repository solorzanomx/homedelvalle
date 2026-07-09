<?php

namespace App\Livewire\Forms;

use App\Models\FormSubmission;
use App\Models\LegalAcceptance;
use App\Models\LegalDocument;
use App\Services\AutomationEngine;
use App\Services\SpamProtectionService;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class LandSellerForm extends Component
{
    // Honeypot — un humano nunca lo llena (oculto por CSS, no type=hidden).
    public string $website_url = '';

    public string $nombre = '';
    public string $email = '';
    public string $whatsapp = '';
    public string $colonia = '';
    public string $tipo_actual = '';
    public ?int $superficie_terreno_m2 = null;
    public string $situacion = '';
    public string $timing = '';
    public bool $aviso = false;

    public bool $submitted = false;
    public bool $isProcessing = false;
    public string $folio = '';
    public string $clientName = '';

    protected function rules(): array
    {
        return [
            'nombre' => 'required|string|max:120',
            'email' => 'required|email',
            'whatsapp' => ['required', 'regex:/^(\+?52)?\s?[0-9]{10}$/'],
            'colonia' => 'required|string|max:160',
            'tipo_actual' => 'required|in:casa_sola,casa_con_local,edificio,terreno',
            'superficie_terreno_m2' => 'nullable|integer|min:1',
            'situacion' => 'required|in:la_habito,rentada,desocupada,sucesion',
            'timing' => 'required|in:inmediato,1_3m,3_6m,solo_explorar',
            'aviso' => 'accepted',
        ];
    }

    protected array $validationAttributes = [
        'nombre' => 'nombre completo',
        'email' => 'email',
        'whatsapp' => 'WhatsApp',
        'colonia' => 'colonia o dirección',
        'tipo_actual' => 'tipo de propiedad',
        'superficie_terreno_m2' => 'superficie de terreno',
        'situacion' => 'situación actual',
        'timing' => 'timing',
        'aviso' => 'aviso de privacidad',
    ];

    public function submit(SpamProtectionService $spam, AutomationEngine $engine): void
    {
        $data = $this->validate(); // valida primero — si falla, isProcessing nunca se bloquea
        if ($this->isProcessing) return;
        $this->isProcessing = true;

        // Honeypot: un bot que lo llena recibe la misma pantalla de éxito
        // sin que nada se guarde (mismo patrón que SellerValuationForm).
        if ($this->website_url !== '') {
            $this->reset();
            $this->submitted = true;
            return;
        }

        $spamCheck = $spam->check($data, null, request()->ip(), 'vendedor_predio');
        if (! $spamCheck['pass']) {
            $this->reset();
            $this->submitted = true;
            return;
        }

        $lockKey = 'form_submit_vendedor_predio_' . md5($data['email']);
        if (! Cache::lock($lockKey, 30)->get()) return;

        // Crear FormSubmission (Lead) directamente - NO crear Client ni Operation
        $submission = FormSubmission::create([
            'form_type'   => 'vendedor_predio',
            'source_page' => '/vende-a-desarrolladora',
            'full_name'   => $data['nombre'],
            'email'       => $data['email'],
            'phone'       => $data['whatsapp'],
            'payload'     => collect($data)->except(['nombre', 'email', 'whatsapp', 'aviso'])->toArray(),
            'lead_tag'    => 'LEAD_PREDIO',
            'client_type' => 'owner',
            'lead_temperature' => $this->calculateLeadTemperature($data),
            'property_type' => $data['tipo_actual'] === 'terreno' ? 'terreno' : 'casa',
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
        ], 'vendedor_predio', notifyAdmins: false);

        $privacyDoc = LegalDocument::where('type', 'aviso_privacidad')->where('status', 'published')->first();
        if ($privacyDoc && $privacyDoc->current_version_id) {
            LegalAcceptance::record(
                $privacyDoc->id,
                $privacyDoc->current_version_id,
                $data['email'],
                request(),
                'vendedor_predio',
                ['name' => $data['nombre']]
            );
        }

        $savedName  = $data['nombre'];
        $savedFolio = 'HDV-' . strtoupper(substr(md5($submission->id . 'vendedor_predio'), 0, 4)) . '-' . $submission->id;

        $this->reset();
        $this->submitted  = true;
        $this->folio      = $savedFolio;
        $this->clientName = $savedName;
    }

    /**
     * Un predio disponible ya (desocupado o timing inmediato) es exactamente
     * lo que la cartera de constructoras busca — se marca caliente.
     */
    private function calculateLeadTemperature(array $data): string
    {
        $isImmediate = $data['timing'] === 'inmediato';
        $isAvailable = in_array($data['situacion'], ['desocupada', 'terreno']) || $data['tipo_actual'] === 'terreno';

        if ($isImmediate && $isAvailable) {
            return 'hot';
        }

        if ($data['timing'] === 'solo_explorar') {
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
        return view('livewire.forms.land-seller-form');
    }
}
