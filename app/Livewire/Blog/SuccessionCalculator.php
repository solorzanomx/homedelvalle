<?php

namespace App\Livewire\Blog;

use App\Models\FormSubmission;
use App\Models\LegalAcceptance;
use App\Models\LegalDocument;
use App\Models\Post;
use App\Models\SuccessionCalculatorConfig;
use App\Rules\RealisticMexicanPhone;
use App\Services\AutomationEngine;
use App\Services\SpamProtectionService;
use App\Support\BenitoJuarezColonias;
use App\Support\BlogWhatsapp;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Livewire\Component;

/**
 * Calculadora de costo de sucesión (Fase 4 del prompt de leads del blog:
 * docs/funcionalidades/blog-calculadora-sucesion.md). Los rangos salen de
 * SuccessionCalculatorConfig (editable en /admin/succession-calculator) — este componente NUNCA
 * calcula con una cifra que no venga de ahí.
 *
 * OPTIMIZACIÓN (post-lanzamiento, a pedido de Alejandro): antes el cálculo era gratis y sin
 * fricción, y solo DESPUÉS de ver el resultado se le pedía el WhatsApp al lector — quien veía el
 * desglose y se iba sin dejar contacto quedaba completamente perdido, sin ningún rastro. Ahora el
 * WhatsApp se pide junto con los datos del inmueble, en el MISMO formulario: quien llega al
 * resultado siempre queda como lead capturado. Sigue siendo "gratis" e instantáneo (nada de "te
 * escribimos en 24h") — solo se movió el único campo de contacto un paso antes, no se agregó fricción.
 */
class SuccessionCalculator extends Component
{
    public int $postId;

    // Entradas del cálculo
    public string $valorInmueble = '';
    public string $conTestamento = '';   // 'si' | 'no'
    public int $numHerederos = 1;
    public string $tieneEscrituras = 'si';   // 'si' | 'no'

    // Contacto — WhatsApp obligatorio, nombre y correo opcionales, en el mismo paso.
    public string $website_url = '';   // honeypot
    public string $name = '';
    public string $whatsapp = '';
    public string $email = '';
    // Colonia real (catálogo de MarketZone/MarketColonia) u "Otra colonia (fuera de Benito
    // Juárez)" — obligatoria: hallazgo 2026-09-30, sin esto no había forma de saber de un
    // vistazo si un lead era un prospecto real de la zona o no.
    public string $colonia = '';
    public bool $aviso = false;

    public bool $started = false;
    public bool $calculated = false;
    public bool $isProcessing = false;
    public ?array $result = null;
    public ?string $whatsappContinueUrl = null;

    /** @var array<string, array<int, string>> */
    public array $coloniaOptions = [];

    public function mount(int $postId): void
    {
        $this->postId = $postId;
        $this->coloniaOptions = BenitoJuarezColonias::grouped();
    }

    public function updated(string $propertyName): void
    {
        if (! $this->started && in_array($propertyName, ['valorInmueble', 'conTestamento', 'numHerederos', 'tieneEscrituras'], true)) {
            $this->started = true;
            $this->dispatch('calculator-event', stage: 'start');
        }
    }

    protected function rules(): array
    {
        return [
            'valorInmueble' => 'required|numeric|min:100000|max:200000000',
            'conTestamento' => 'required|in:si,no',
            'numHerederos' => 'required|integer|min:1|max:20',
            'tieneEscrituras' => 'required|in:si,no',
            'whatsapp' => ['required', 'regex:/^(\+?52)?\s?[0-9]{10}$/', new RealisticMexicanPhone()],
            'colonia' => ['required', Rule::in(BenitoJuarezColonias::validValues())],
            'name' => 'nullable|string|max:120',
            'email' => 'nullable|email|max:150',
            'aviso' => 'accepted',
        ];
    }

    protected array $validationAttributes = [
        'valorInmueble' => 'valor del inmueble', 'conTestamento' => 'testamento', 'numHerederos' => 'número de herederos',
        'tieneEscrituras' => 'escrituras', 'whatsapp' => 'WhatsApp', 'colonia' => 'colonia',
        'name' => 'nombre', 'email' => 'correo', 'aviso' => 'aviso de privacidad',
    ];

    /** Calcula Y captura el lead en un solo paso — ver el porqué en el docblock de la clase. */
    public function calculate(SpamProtectionService $spam): void
    {
        $data = $this->validate();
        if ($this->isProcessing) {
            return;
        }
        $this->isProcessing = true;

        if ($this->website_url !== '') {
            $this->isProcessing = false;
            return;
        }

        $spamCheck = $spam->check($data, null, request()->ip(), 'contacto');
        if (! $spamCheck['pass']) {
            $this->isProcessing = false;
            return;
        }

        $scenario = $data['conTestamento'] === 'si' ? SuccessionCalculatorConfig::CON_TESTAMENTO : SuccessionCalculatorConfig::SIN_TESTAMENTO;
        $config = SuccessionCalculatorConfig::forScenario($scenario);
        if (! $config) {
            $this->isProcessing = false;
            return;
        }

        $this->result = $config->estimate((float) $data['valorInmueble'], (int) $data['numHerederos'], $data['tieneEscrituras'] === 'si');
        $this->calculated = true;
        $this->dispatch('calculator-event', stage: 'complete', conTestamento: $data['conTestamento'], validated: $this->result['validated'] ? '1' : '0');

        $lockKey = 'form_submit_succession_calc_' . md5($data['whatsapp']);
        if (! Cache::lock($lockKey, 30)->get()) {
            // Ya se calculó y se le muestra el resultado igual — el candado solo evita duplicar el
            // lead si reenvía el mismo formulario, no le niega el cálculo que ya pidió.
            $this->isProcessing = false;
            return;
        }

        $post = Post::find($this->postId);

        FormSubmission::create([
            'form_type' => 'vendedor_predio',
            'source_page' => $post ? '/blog/' . $post->slug : '/blog',
            'full_name' => $data['name'] ?: 'Lead del blog',
            'email' => $data['email'] ?: null,
            'phone' => $data['whatsapp'],
            'payload' => [
                'origen' => 'blog_calculadora_sucesion',
                'valor_inmueble' => (float) $data['valorInmueble'],
                'con_testamento' => $data['conTestamento'],
                'num_herederos' => $data['numHerederos'],
                'tiene_escrituras' => $data['tieneEscrituras'],
                'colonia' => $data['colonia'],
                'estimado_min' => $this->result['total_min'],
                'estimado_max' => $this->result['total_max'],
                'rangos_validados' => $this->result['validated'],
            ],
            'lead_tag' => 'LEAD_CALCULADORA_SUCESION',
            'client_type' => 'owner',
            'lead_temperature' => 'hot',
            'referrer' => request()->headers->get('referer'),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        if ($data['email']) {
            app(AutomationEngine::class)->processFormSubmitted([
                'name' => $data['name'] ?: 'Lead del blog', 'email' => $data['email'], 'phone' => $data['whatsapp'],
            ], 'vendedor_predio', notifyAdmins: false);
        }

        $privacyDoc = LegalDocument::where('type', 'aviso_privacidad')->where('status', 'published')->first();
        if ($privacyDoc && $privacyDoc->current_version_id) {
            LegalAcceptance::record($privacyDoc->id, $privacyDoc->current_version_id, $data['email'] ?: $data['whatsapp'], request(), 'vendedor_predio', ['name' => $data['name']]);
        }

        $this->whatsappContinueUrl = BlogWhatsapp::urlFor(
            $post,
            'Hola, vengo del artículo "{titulo}". Usé la calculadora: mi estimado fue de $' . number_format($this->result['total_min']) . ' a $' . number_format($this->result['total_max']) . '. Quiero el desglose exacto para mi caso y saber cuánto pagaría de ISR si vendo después.'
        );

        $this->reset(['website_url', 'name', 'whatsapp', 'email', 'colonia', 'aviso', 'isProcessing']);
        $this->dispatch('lead-conversion', formType: 'vendedor_predio', variant: 'calculadora_sucesion');
    }

    public function render()
    {
        return view('livewire.blog.succession-calculator');
    }
}
