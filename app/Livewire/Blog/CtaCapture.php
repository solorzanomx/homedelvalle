<?php

namespace App\Livewire\Blog;

use App\Models\BlogCtaConfig;
use App\Models\FormSubmission;
use App\Models\LegalAcceptance;
use App\Models\LegalDocument;
use App\Models\Post;
use App\Rules\RealisticMexicanPhone;
use App\Services\AutomationEngine;
use App\Services\SpamProtectionService;
use App\Support\BenitoJuarezColonias;
use App\Support\BlogCluster;
use App\Support\BlogWhatsapp;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Livewire\Component;

/**
 * El CTA por cluster del blog (Fase 3 del prompt de leads: docs/funcionalidades/blog-cta-clusters.md).
 * Un solo componente para las 2 posiciones (inline y final) — mismo copy, mismo formulario corto
 * (WhatsApp obligatorio, nombre y correo opcionales), sin promesa de "en 24 horas": la respuesta va
 * en pantalla al enviar, con un botón para seguir por WhatsApp. El correo es opcional (Fase 3
 * original: "sin email obligatorio") pero si lo dan, sí se usa: habilita el acuse automático y las
 * automatizaciones de AutomationEngine, que sin correo no pueden correr (optimización post-lanzamiento).
 */
class CtaCapture extends Component
{
    // Honeypot — un humano nunca lo llena (oculto por CSS, no type=hidden). Mismo patrón que
    // BlogQuickValuationForm/SellerValuationForm.
    public string $website_url = '';

    public int $postId;
    public string $location = 'inline';   // 'inline' | 'final' — solo para el param de tracking

    public string $name = '';
    public string $whatsapp = '';
    // Opcional a propósito (Fase 3: "sin email obligatorio") — pero si lo dan, sí sirve: habilita
    // el acuse automático y las automatizaciones que dependen de correo (AutomationEngine).
    public string $email = '';
    // Colonia real (catálogo de MarketZone/MarketColonia) u "Otra colonia (fuera de Benito
    // Juárez)" — obligatoria: hallazgo 2026-09-30, sin esto no había forma de saber de un
    // vistazo si un lead era un prospecto real de la zona o no.
    public string $colonia = '';
    public bool $aviso = false;

    public bool $submitted = false;
    public bool $isProcessing = false;

    public string $headline = '';
    public string $body = '';
    public string $buttonLabel = '';
    public string $cluster = '';
    public ?string $whatsappContinueUrl = null;

    /** @var array<string, array<int, string>> */
    public array $coloniaOptions = [];

    public function mount(int $postId, string $location = 'inline'): void
    {
        $this->postId = $postId;
        $this->location = $location;
        $this->coloniaOptions = BenitoJuarezColonias::grouped();

        $post = Post::find($postId);
        $this->cluster = BlogCluster::forPost($post) ?? '';
        $config = BlogCtaConfig::forCluster($this->cluster ?: null);
        $decided = BlogCluster::showsSellCta($post);

        if ($config) {
            $copy = $config->copyFor($decided);
            $this->headline = $copy['headline'];
            $this->body = $copy['body'];
            $this->buttonLabel = $copy['button_label'];
        } else {
            // Sin cluster (o sin fila de config, no debería pasar tras el seed): mismo fallback
            // genérico que ya usaba el CTA automático por categoría.
            $this->headline = '¿Tienes una propiedad en la Benito Juárez?';
            $this->body = 'Platícanos tu caso. Asesoría personalizada, sin costo y sin compromiso.';
            $this->buttonLabel = 'Contactar a un asesor';
        }
    }

    protected function rules(): array
    {
        return [
            'name' => 'nullable|string|max:120',
            'whatsapp' => ['required', 'regex:/^(\+?52)?\s?[0-9]{10}$/', new RealisticMexicanPhone()],
            'colonia' => ['required', Rule::in(BenitoJuarezColonias::validValues())],
            'email' => 'nullable|email|max:150',
            'aviso' => 'accepted',
        ];
    }

    protected array $validationAttributes = [
        'name' => 'nombre', 'whatsapp' => 'WhatsApp', 'colonia' => 'colonia',
        'email' => 'correo', 'aviso' => 'aviso de privacidad',
    ];

    public function submit(SpamProtectionService $spam): void
    {
        $data = $this->validate();
        if ($this->isProcessing) {
            return;
        }
        $this->isProcessing = true;

        if ($this->website_url !== '') {
            $this->resetForm();
            return;
        }

        $spamCheck = $spam->check($data, null, request()->ip(), 'contacto');
        if (! $spamCheck['pass']) {
            $this->resetForm();
            return;
        }

        $lockKey = 'form_submit_blog_cta_' . md5($data['whatsapp']);
        if (! Cache::lock($lockKey, 30)->get()) {
            return;
        }

        $post = Post::find($this->postId);
        $decided = BlogCluster::showsSellCta($post);
        $config = BlogCtaConfig::forCluster($this->cluster ?: null);
        $formType = $config->form_type ?? 'contacto';

        FormSubmission::create([
            'form_type' => $formType,
            'source_page' => $post ? '/blog/' . $post->slug : '/blog',
            // full_name es NOT NULL en form_submissions (todos los forms anteriores lo exigían) —
            // como aquí el nombre es opcional, un fallback legible en vez de forzar otra columna nullable.
            'full_name' => $data['name'] ?: 'Lead del blog',
            'email' => $data['email'] ?: null,
            'phone' => $data['whatsapp'],
            'payload' => [
                'origen' => 'blog_cta', 'cluster' => $this->cluster, 'cta_variant' => $decided ? 'decidido' : 'default',
                'cta_location' => $this->location, 'colonia' => $data['colonia'],
            ],
            'lead_tag' => 'LEAD_BLOG',
            'client_type' => in_array($formType, ['vendedor', 'vendedor_predio'], true) ? 'owner' : null,
            'lead_temperature' => $decided ? 'hot' : 'warm',
            'referrer' => request()->headers->get('referer'),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        // AutomationEngine::processFormSubmitted() exige email (ver docstring de la clase) — este
        // form no lo pide, pero si lo dieron (campo opcional), sí lo llamamos: activa el flujo
        // normal de automatizaciones igual que cualquier otro formulario del sitio.
        if ($data['email']) {
            app(AutomationEngine::class)->processFormSubmitted([
                'name' => $data['name'] ?: 'Lead del blog', 'email' => $data['email'], 'phone' => $data['whatsapp'],
            ], $formType, notifyAdmins: false);
        }

        $privacyDoc = LegalDocument::where('type', 'aviso_privacidad')->where('status', 'published')->first();
        if ($privacyDoc && $privacyDoc->current_version_id) {
            // LegalAcceptance::record() guarda el identificador en la columna 'email' sin validar
            // formato — si no dieron correo, se usa el WhatsApp como identificador.
            LegalAcceptance::record($privacyDoc->id, $privacyDoc->current_version_id, $data['email'] ?: $data['whatsapp'], request(), $formType, ['name' => $data['name']]);
        }

        $template = $config?->copyFor($decided)['whatsapp_message'] ?? 'Hola, vengo del artículo "{titulo}".';
        $this->whatsappContinueUrl = BlogWhatsapp::urlFor($post, $template);
        $this->resetForm();
        $this->dispatch('lead-conversion', formType: $formType, variant: $this->cluster ?: 'default');
    }

    private function resetForm(): void
    {
        $this->reset(['website_url', 'name', 'whatsapp', 'email', 'colonia', 'aviso', 'isProcessing']);
        $this->submitted = true;
    }

    public function render()
    {
        return view('livewire.blog.cta-capture');
    }
}
