<?php

namespace App\Livewire\Forms;

use App\Models\FormSubmission;
use App\Models\Client;
use App\Models\Operation;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class SellerValuationForm extends Component
{
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

    public function submit(): void
    {
        if ($this->isProcessing) return;
        $this->isProcessing = true;

        $data = $this->validate();

        $lockKey = 'form_submit_vendedor_' . md5($data['email']);
        if (! Cache::lock($lockKey, 30)->get()) return;

        // Crear o actualizar Cliente
        $client = Client::firstOrCreate(
            ['email' => $data['email']],
            [
                'name' => $data['nombre'],
                'email' => $data['email'],
                'phone' => $data['whatsapp'],
                'whatsapp' => $data['whatsapp'],
                'client_type' => 'owner',
                'lead_temperature' => $this->calculateLeadTemperature($data),
                'property_type' => $data['tipo_propiedad'],
                'initial_notes' => "Propiedad en {$data['colonia']}, motivo: {$data['motivo']}, estado doc: {$data['estado_doc']}",
                'lead_source' => '/vende-tu-propiedad',
                'utm_source' => request()->query('utm_source'),
                'utm_medium' => request()->query('utm_medium'),
                'utm_campaign' => request()->query('utm_campaign'),
            ]
        );

        // Crear Operation para captación
        $operation = Operation::create([
            'client_id' => $client->id,
            'type' => 'captacion',
            'stage' => 'contacto',
            'status' => 'activo',
            'notes' => "Solicitud de valuación: {$data['tipo_propiedad']} en {$data['colonia']}, {$data['superficie_m2']}m², {$data['recamaras']} recámaras. Precio esperado: {$data['precio_esperado']}, Timing: {$data['timing']}",
        ]);

        $submission = FormSubmission::create([
            'form_type'   => 'vendedor',
            'source_page' => '/vende-tu-propiedad',
            'full_name'   => $data['nombre'],
            'email'       => $data['email'],
            'phone'       => $data['whatsapp'],
            'payload'     => collect($data)->except(['nombre', 'email', 'whatsapp', 'aviso'])->toArray(),
            'lead_tag'    => 'LEAD_VENDEDOR',
            'client_id'   => $client->id,
            'utm_source'  => request()->query('utm_source'),
            'utm_medium'  => request()->query('utm_medium'),
            'utm_campaign'=> request()->query('utm_campaign'),
            'referrer'    => request()->headers->get('referer'),
            'ip'          => request()->ip(),
            'user_agent'  => request()->userAgent(),
        ]);

        $savedName  = $data['nombre'];
        $savedFolio = 'HDV-' . strtoupper(substr(md5($submission->id . 'vendedor'), 0, 4)) . '-' . $submission->id;

        $this->reset();
        $this->submitted  = true;
        $this->folio      = $savedFolio;
        $this->clientName = $savedName;
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

    public function render()
    {
        return view('livewire.forms.seller-valuation-form');
    }
}

