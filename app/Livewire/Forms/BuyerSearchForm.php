<?php

namespace App\Livewire\Forms;

use App\Models\FormSubmission;
use Livewire\Component;
use Livewire\WithFileUploads;

class BuyerSearchForm extends Component
{
    use WithFileUploads;

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
    public string $folio = '';
    public string $clientName = '';

    protected function rules(): array
    {
        return [
            'tipo_inmueble' => 'required|array|min:1',
            'tipo_inmueble.*' => 'in:departamento,casa,terreno,oficina,comercial',
            'operacion' => 'required|in:compra,renta',
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

    public function submit(): void
    {
        $data = $this->validate();

        $submission = FormSubmission::create([
            'form_type'   => 'comprador',
            'source_page' => '/comprar',
            'full_name'   => $data['nombre'],
            'email'       => $data['email'],
            'phone'       => $data['whatsapp'],
            'payload'     => collect($data)->except(['nombre', 'email', 'whatsapp', 'aviso'])->toArray(),
            'lead_tag'    => 'LEAD_COMPRADOR',
            'utm_source'  => request()->query('utm_source'),
            'utm_medium'  => request()->query('utm_medium'),
            'utm_campaign'=> request()->query('utm_campaign'),
            'referrer'    => request()->headers->get('referer'),
            'ip'          => request()->ip(),
            'user_agent'  => request()->userAgent(),
        ]);

        $savedName  = $data['nombre'];
        $savedFolio = 'HDV-' . strtoupper(substr(md5($submission->id . 'comprador'), 0, 4)) . '-' . $submission->id;

        $this->reset();
        $this->submitted  = true;
        $this->folio      = $savedFolio;
        $this->clientName = $savedName;
    }

    public function render()
    {
        return view('livewire.forms.buyer-search-form');
    }
}
