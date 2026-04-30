<?php

namespace App\Livewire\Forms;

use App\Models\FormSubmission;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class RentalOwnerForm extends Component
{
    public string $nombre          = '';
    public string $email           = '';
    public string $whatsapp        = '';
    public string $tipo_propiedad  = '';
    public string $colonia         = '';
    public ?int   $superficie_m2   = null;
    public string $recamaras       = '';
    public string $amueblado       = '';
    public string $renta_esperada  = '';
    public string $plazo_minimo    = '';
    public string $mascotas_acepta = '';
    public string $estado_doc      = '';
    public string $administracion  = '';
    public string $poliza          = '';
    public string $timing          = '';
    public bool   $aviso           = false;

    public bool   $submitted       = false;
    public bool   $isProcessing    = false;
    public string $folio           = '';
    public string $clientName      = '';

    protected function rules(): array
    {
        return [
            'nombre'          => 'required|string|max:120',
            'email'           => 'required|email',
            'whatsapp'        => ['required', 'regex:/^(\+?52)?\s?[0-9]{10}$/'],
            'tipo_propiedad'  => 'required|in:departamento,casa,estudio,loft,oficina,local_comercial',
            'colonia'         => 'required|string|max:160',
            'superficie_m2'   => 'nullable|integer|min:1',
            'recamaras'       => 'nullable|in:1,2,3,4_plus,na',
            'amueblado'       => 'required|in:completo,parcial,no',
            'renta_esperada'  => 'required|in:hasta_15k,15k_25k,25k_40k,40k_70k,70k_plus,no_se',
            'plazo_minimo'    => 'required|in:6m,12m,24m,sin_preferencia',
            'mascotas_acepta' => 'required|in:si,no,depende',
            'estado_doc'      => 'required|in:al_corriente,pendientes,sucesion,no_se',
            'administracion'  => 'required|in:si_quiero,solo_inquilino,quiero_conocer',
            'poliza'          => 'required|in:obligatoria,si_sin_aval,prefiero_aval,no_se',
            'timing'          => 'required|in:inmediato,2_4sem,1_3m,sin_prisa',
            'aviso'           => 'accepted',
        ];
    }

    protected array $validationAttributes = [
        'nombre'          => 'nombre completo',
        'email'           => 'email',
        'whatsapp'        => 'WhatsApp',
        'tipo_propiedad'  => 'tipo de propiedad',
        'colonia'         => 'colonia o dirección',
        'superficie_m2'   => 'superficie',
        'recamaras'       => 'recámaras',
        'amueblado'       => '¿está amueblado?',
        'renta_esperada'  => 'renta mensual esperada',
        'plazo_minimo'    => 'plazo mínimo del contrato',
        'mascotas_acepta' => '¿aceptas mascotas?',
        'estado_doc'      => 'estado documental',
        'administracion'  => '¿te interesa administración integral?',
        'poliza'          => '¿buscas póliza jurídica?',
        'timing'          => 'timing para colocar',
        'aviso'           => 'aviso de privacidad',
    ];

    public function submit(): void
    {
        $data = $this->validate(); // valida primero — si falla, isProcessing nunca se bloquea
        if ($this->isProcessing) return;
        $this->isProcessing = true;

        $lockKey = 'form_submit_propietario_renta_' . md5($data['email']);
        if (! Cache::lock($lockKey, 30)->get()) return;

        $isHot = $data['timing'] === 'inmediato' && $data['estado_doc'] === 'al_corriente';

        $submission = FormSubmission::create([
            'form_type'        => 'propietario_renta',
            'source_page'      => '/renta-tu-propiedad',
            'full_name'        => $data['nombre'],
            'email'            => $data['email'],
            'phone'            => $data['whatsapp'],
            'payload'          => collect($data)->except(['nombre', 'email', 'whatsapp', 'aviso'])->toArray(),
            'lead_tag'         => 'LEAD_PROPIETARIO_RENTA',
            'client_type'      => 'owner',
            'lead_temperature' => $isHot ? 'hot' : 'warm',
            'property_type'    => $data['tipo_propiedad'],
            'utm_source'       => request()->query('utm_source'),
            'utm_medium'       => request()->query('utm_medium'),
            'utm_campaign'     => request()->query('utm_campaign'),
            'referrer'         => request()->headers->get('referer'),
            'ip'               => request()->ip(),
            'user_agent'       => request()->userAgent(),
        ]);

        $savedName  = $data['nombre'];
        $savedFolio = 'HDV-PR-' . strtoupper(substr(md5($submission->id . 'propietario_renta'), 0, 4)) . '-' . $submission->id;

        $this->reset();
        $this->submitted  = true;
        $this->folio      = $savedFolio;
        $this->clientName = $savedName;
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
        return view('livewire.forms.rental-owner-form');
    }
}
