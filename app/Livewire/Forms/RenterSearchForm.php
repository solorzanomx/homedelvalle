<?php

namespace App\Livewire\Forms;

use App\Models\FormSubmission;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class RenterSearchForm extends Component
{
    public array  $tipo_inmueble  = [];
    public array  $zonas          = [];
    public string $recamaras      = '';
    public string $renta_mensual  = '';
    public string $plazo_contrato = '';
    public string $mascotas       = '';
    public string $garantia       = '';
    public string $timing         = '';
    public string $must_have      = '';
    public string $nombre         = '';
    public string $email          = '';
    public string $whatsapp       = '';
    public bool   $aviso          = false;

    public bool   $submitted      = false;
    public bool   $isProcessing   = false;
    public string $folio          = '';
    public string $clientName     = '';

    protected function rules(): array
    {
        return [
            'tipo_inmueble'   => 'required|array|min:1',
            'tipo_inmueble.*' => 'in:departamento,casa,estudio,loft,oficina,casa_jardin',
            'zonas'           => 'required|array|min:1',
            'recamaras'       => 'required|in:1,2,3,4_plus,sin_preferencia',
            'renta_mensual'   => 'required|in:hasta_15k,15k_25k,25k_40k,40k_70k,70k_plus',
            'plazo_contrato'  => 'required|in:6m,12m,24m_plus,flexible',
            'mascotas'        => 'required|in:perro,gato,otra,no',
            'garantia'        => 'required|in:aval_propiedad,poliza_juridica,deposito_ampliado,no_decido',
            'timing'          => 'required|in:inmediato,2_4sem,1_3m,explorando',
            'must_have'       => 'nullable|string|max:280',
            'nombre'          => 'required|string|max:120',
            'email'           => 'required|email',
            'whatsapp'        => ['required', 'regex:/^(\+?52)?\s?[0-9]{10}$/'],
            'aviso'           => 'accepted',
        ];
    }

    protected array $validationAttributes = [
        'tipo_inmueble'   => 'tipo de inmueble',
        'zonas'           => 'zonas de interés',
        'recamaras'       => 'recámaras',
        'renta_mensual'   => 'renta mensual deseada',
        'plazo_contrato'  => 'plazo del contrato',
        'mascotas'        => '¿vives con mascotas?',
        'garantia'        => 'forma de garantía',
        'timing'          => 'timing de mudanza',
        'must_have'       => 'especificaciones',
        'nombre'          => 'nombre completo',
        'email'           => 'email',
        'whatsapp'        => 'WhatsApp',
        'aviso'           => 'aviso de privacidad',
    ];

    public function submit(): void
    {
        if ($this->isProcessing) return;
        $this->isProcessing = true;
        $data = $this->validate();

        $lockKey = 'form_submit_rentar_' . md5($data['email']);
        if (! Cache::lock($lockKey, 30)->get()) return;

        $submission = FormSubmission::create([
            'form_type'        => 'arrendatario',
            'source_page'      => '/rentar',
            'full_name'        => $data['nombre'],
            'email'            => $data['email'],
            'phone'            => $data['whatsapp'],
            'payload'          => collect($data)->except(['nombre', 'email', 'whatsapp', 'aviso'])->toArray(),
            'lead_tag'         => 'LEAD_ARRENDATARIO',
            'client_type'      => 'renter',
            'lead_temperature' => $data['timing'] === 'inmediato' ? 'hot' : 'warm',
            'interest_types'   => $data['tipo_inmueble'],
            'utm_source'       => request()->query('utm_source'),
            'utm_medium'       => request()->query('utm_medium'),
            'utm_campaign'     => request()->query('utm_campaign'),
            'referrer'         => request()->headers->get('referer'),
            'ip'               => request()->ip(),
            'user_agent'       => request()->userAgent(),
        ]);

        $savedName  = $data['nombre'];
        $savedFolio = 'HDV-RENTA-' . strtoupper(substr(md5($submission->id . 'arrendatario'), 0, 4)) . '-' . $submission->id;

        $this->reset();
        $this->submitted  = true;
        $this->folio      = $savedFolio;
        $this->clientName = $savedName;
    }

    public function render()
    {
        return view('livewire.forms.renter-search-form');
    }
}
