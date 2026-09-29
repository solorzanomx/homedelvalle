<?php

namespace App\Livewire\Blog;

use App\Models\FormSubmission;
use App\Models\LegalAcceptance;
use App\Models\LegalDocument;
use App\Models\Post;
use App\Models\SuccessionCalculatorConfig;
use App\Services\SpamProtectionService;
use App\Support\BlogWhatsapp;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

/**
 * Calculadora de costo de sucesión (Fase 4 del prompt de leads del blog:
 * docs/funcionalidades/blog-calculadora-sucesion.md). Los rangos salen de
 * SuccessionCalculatorConfig (editable en /admin/succession-calculator) — este componente NUNCA
 * calcula con una cifra que no venga de ahí.
 */
class SuccessionCalculator extends Component
{
    public int $postId;

    // Entradas
    public string $valorInmueble = '';
    public string $conTestamento = '';   // 'si' | 'no'
    public int $numHerederos = 1;
    public string $tieneEscrituras = 'si';   // 'si' | 'no'

    public bool $started = false;
    public bool $calculated = false;
    public ?array $result = null;

    // Captura corta tras el resultado — mismo patrón que CtaCapture (WhatsApp obligatorio, nombre
    // opcional, sin email), pero con los datos de la calculadora en el payload del lead.
    public string $website_url = '';
    public string $name = '';
    public string $whatsapp = '';
    public bool $aviso = false;
    public bool $submitted = false;
    public bool $isProcessing = false;
    public ?string $whatsappContinueUrl = null;

    public function mount(int $postId): void
    {
        $this->postId = $postId;
    }

    public function updated(string $propertyName): void
    {
        if (! $this->started && in_array($propertyName, ['valorInmueble', 'conTestamento', 'numHerederos', 'tieneEscrituras'], true)) {
            $this->started = true;
            $this->dispatch('calculator-event', stage: 'start');
        }
    }

    protected function calcRules(): array
    {
        return [
            'valorInmueble' => 'required|numeric|min:100000|max:200000000',
            'conTestamento' => 'required|in:si,no',
            'numHerederos' => 'required|integer|min:1|max:20',
            'tieneEscrituras' => 'required|in:si,no',
        ];
    }

    public function calculate(): void
    {
        $data = $this->validate($this->calcRules());

        $scenario = $data['conTestamento'] === 'si' ? SuccessionCalculatorConfig::CON_TESTAMENTO : SuccessionCalculatorConfig::SIN_TESTAMENTO;
        $config = SuccessionCalculatorConfig::forScenario($scenario);
        if (! $config) {
            return;
        }

        $this->result = $config->estimate((float) $data['valorInmueble'], (int) $data['numHerederos'], $data['tieneEscrituras'] === 'si');
        $this->calculated = true;

        $this->dispatch('calculator-event', stage: 'complete', conTestamento: $data['conTestamento'], validated: $this->result['validated'] ? '1' : '0');
    }

    protected function leadRules(): array
    {
        return [
            'name' => 'nullable|string|max:120',
            'whatsapp' => ['required', 'regex:/^(\+?52)?\s?[0-9]{10}$/'],
            'aviso' => 'accepted',
        ];
    }

    protected array $validationAttributes = ['name' => 'nombre', 'whatsapp' => 'WhatsApp', 'aviso' => 'aviso de privacidad'];

    public function submitLead(SpamProtectionService $spam): void
    {
        $data = $this->validate($this->leadRules());
        if ($this->isProcessing || ! $this->result) {
            return;
        }
        $this->isProcessing = true;

        if ($this->website_url !== '') {
            $this->resetLeadForm();
            return;
        }

        $spamCheck = $spam->check($data, null, request()->ip(), 'contacto');
        if (! $spamCheck['pass']) {
            $this->resetLeadForm();
            return;
        }

        $lockKey = 'form_submit_succession_calc_' . md5($data['whatsapp']);
        if (! Cache::lock($lockKey, 30)->get()) {
            return;
        }

        $post = Post::find($this->postId);

        FormSubmission::create([
            'form_type' => 'vendedor_predio',
            'source_page' => $post ? '/blog/' . $post->slug : '/blog',
            'full_name' => $data['name'] ?: 'Lead del blog',
            'phone' => $data['whatsapp'],
            'payload' => [
                'origen' => 'blog_calculadora_sucesion',
                'valor_inmueble' => (float) $this->valorInmueble,
                'con_testamento' => $this->conTestamento,
                'num_herederos' => $this->numHerederos,
                'tiene_escrituras' => $this->tieneEscrituras,
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

        $privacyDoc = LegalDocument::where('type', 'aviso_privacidad')->where('status', 'published')->first();
        if ($privacyDoc && $privacyDoc->current_version_id) {
            LegalAcceptance::record($privacyDoc->id, $privacyDoc->current_version_id, $data['whatsapp'], request(), 'vendedor_predio', ['name' => $data['name']]);
        }

        $this->whatsappContinueUrl = BlogWhatsapp::urlFor(
            $post,
            'Hola, vengo del artículo "{titulo}". Usé la calculadora: mi estimado fue de $' . number_format($this->result['total_min']) . ' a $' . number_format($this->result['total_max']) . '. Quiero el desglose exacto para mi caso y saber cuánto pagaría de ISR si vendo después.'
        );
        $this->resetLeadForm();
        $this->dispatch('lead-conversion', formType: 'vendedor_predio', variant: 'calculadora_sucesion');
    }

    private function resetLeadForm(): void
    {
        $this->reset(['website_url', 'name', 'whatsapp', 'aviso', 'isProcessing']);
        $this->submitted = true;
    }

    public function render()
    {
        return view('livewire.blog.succession-calculator');
    }
}
