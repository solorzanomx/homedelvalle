<?php

namespace App\Livewire\Forms;

use App\Models\FormSubmission;
use App\Models\LegalAcceptance;
use App\Models\LegalDocument;
use App\Services\AutomationEngine;
use App\Services\SpamProtectionService;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

/**
 * Mini-formulario de valuación embebido a media lectura en los posts del
 * blog (ver BlogBodyEnhancer) — captura al lector en el momento del
 * interés sin sacarlo del artículo. Versión corta de SellerValuationForm:
 * mismo funnel (form_type 'vendedor'), mismos guards; el origen queda
 * distinguible por source_page (la URL del post) y la atribución
 * automática de HasAttribution ("Blog: {título}").
 */
class BlogQuickValuationForm extends Component
{
    // Honeypot — un humano nunca lo llena (oculto por CSS, no type=hidden).
    public string $website_url = '';

    /** URL del post donde vive el form — la pasa blog/show al montar. */
    public string $sourcePage = '/blog';

    /** Copy alterna para posts de herencias — mismo backend, solo cambia
     *  el texto del form para alinear con la intención real del lector
     *  (quiere saber su ISR, no "el precio real de su propiedad"). */
    public bool $isHerencia = false;

    public string $nombre = '';
    public string $email = '';
    public string $whatsapp = '';
    public string $colonia = '';
    public bool $aviso = false;

    public bool $submitted = false;
    public bool $isProcessing = false;
    public string $clientName = '';

    protected function rules(): array
    {
        return [
            'nombre' => 'required|string|max:120',
            'email' => 'required|email',
            'whatsapp' => ['required', 'regex:/^(\+?52)?\s?[0-9]{10}$/'],
            'colonia' => 'required|string|max:160',
            'aviso' => 'accepted',
        ];
    }

    protected array $validationAttributes = [
        'nombre' => 'nombre',
        'email' => 'email',
        'whatsapp' => 'WhatsApp',
        'colonia' => 'colonia',
        'aviso' => 'aviso de privacidad',
    ];

    public function submit(SpamProtectionService $spam, AutomationEngine $engine): void
    {
        $data = $this->validate();
        if ($this->isProcessing) return;
        $this->isProcessing = true;

        if ($this->website_url !== '') {
            $this->resetForm();
            return;
        }

        $spamCheck = $spam->check($data, null, request()->ip(), 'vendedor');
        if (! $spamCheck['pass']) {
            $this->resetForm();
            return;
        }

        $lockKey = 'form_submit_blog_quick_' . md5($data['email']);
        if (! Cache::lock($lockKey, 30)->get()) return;

        FormSubmission::create([
            'form_type'   => 'vendedor',
            'source_page' => $this->sourcePage,
            'full_name'   => $data['nombre'],
            'email'       => $data['email'],
            'phone'       => $data['whatsapp'],
            'payload'     => ['colonia' => $data['colonia'], 'origen' => 'blog_quick_form', 'lead_variant' => $this->isHerencia ? 'isr' : 'default'],
            'lead_tag'    => 'LEAD_VENDEDOR',
            'client_type' => 'owner',
            'lead_temperature' => 'warm',
            'referrer'    => request()->headers->get('referer'),
            'ip'          => request()->ip(),
            'user_agent'  => request()->userAgent(),
        ]);

        $engine->processFormSubmitted([
            'name' => $data['nombre'],
            'email' => $data['email'],
            'phone' => $data['whatsapp'],
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

        $savedName = $data['nombre'];
        $this->resetForm();
        $this->clientName = $savedName;
        $this->dispatch('lead-conversion', formType: 'vendedor', variant: $this->isHerencia ? 'isr' : 'default');
    }

    /** reset() sin tocar sourcePage — se pasa al montar y debe sobrevivir. */
    private function resetForm(): void
    {
        $this->reset(['website_url', 'nombre', 'email', 'whatsapp', 'colonia', 'aviso', 'isProcessing']);
        $this->submitted = true;
    }

    public function updated(string $propertyName): void
    {
        if ($this->getErrorBag()->has($propertyName)) {
            $this->validateOnly($propertyName);
        }
    }

    public function render()
    {
        return view('livewire.forms.blog-quick-valuation-form');
    }
}
