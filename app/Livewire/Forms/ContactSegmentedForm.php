<?php

namespace App\Livewire\Forms;

use App\Models\FormSubmission;
use App\Models\Client;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class ContactSegmentedForm extends Component
{
    public string $intento = '';
    public string $nombre = '';
    public string $email = '';
    public string $whatsapp = '';
    public string $colonia = '';
    public string $mensaje = '';
    public bool $aviso = false;

    public bool $submitted = false;
    public bool $isProcessing = false;
    public string $folio = '';
    public string $clientName = '';

    protected function rules(): array
    {
        return [
            'intento' => 'required|in:vender,comprar,b2b,admin,legal,otro',
            'nombre' => 'required|string|max:120',
            'email' => 'required|email',
            'whatsapp' => ['required', 'regex:/^(\+?52)?\s?[0-9]{10}$/'],
            'colonia' => 'nullable|string|max:160',
            'mensaje' => 'nullable|string|max:1000',
            'aviso' => 'accepted',
        ];
    }

    protected array $validationAttributes = [
        'intento' => '¿en qué te podemos ayudar?',
        'nombre' => 'nombre completo',
        'email' => 'email',
        'whatsapp' => 'WhatsApp',
        'colonia' => 'colonia',
        'mensaje' => 'mensaje',
        'aviso' => 'aviso de privacidad',
    ];

    public function submit(): void
    {
        if ($this->isProcessing) return;
        $this->isProcessing = true;
        $data = $this->validate();

        $tagMap = [
            'vender' => 'LEAD_VENDEDOR',
            'comprar' => 'LEAD_COMPRADOR',
            'b2b' => 'LEAD_B2B',
            'admin' => 'LEAD_ADMIN',
            'legal' => 'LEAD_LEGAL',
            'otro' => 'LEAD_OTRO',
        ];

        $clientTypeMap = [
            'vender' => 'owner',
            'comprar' => 'buyer',
            'b2b' => 'investor',
            'admin' => 'owner',
            'legal' => 'owner',
            'otro' => null,
        ];

        $lockKey = 'form_submit_contacto_' . md5($data['email']);
        if (! Cache::lock($lockKey, 30)->get()) return;

        // Crear o actualizar Cliente (solo si tiene client_type válido)
        if ($clientTypeMap[$data['intento']]) {
            $client = Client::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['nombre'],
                    'email' => $data['email'],
                    'phone' => $data['whatsapp'],
                    'whatsapp' => $data['whatsapp'],
                    'client_type' => $clientTypeMap[$data['intento']],
                    'lead_temperature' => 'warm',
                    'initial_notes' => $data['mensaje'] ?? "Contacto general desde /contacto",
                    'lead_source' => '/contacto',
                    'utm_source' => request()->query('utm_source'),
                    'utm_medium' => request()->query('utm_medium'),
                    'utm_campaign' => request()->query('utm_campaign'),
                ]
            );
        } else {
            $client = null;
        }

        $submission = FormSubmission::create([
            'form_type'   => 'contacto',
            'source_page' => '/contacto',
            'full_name'   => $data['nombre'],
            'email'       => $data['email'],
            'phone'       => $data['whatsapp'],
            'payload'     => collect($data)->except(['nombre', 'email', 'whatsapp', 'aviso'])->toArray(),
            'lead_tag'    => $tagMap[$data['intento']] ?? 'LEAD_OTRO',
            'client_id'   => $client?->id,
            'utm_source'  => request()->query('utm_source'),
            'utm_medium'  => request()->query('utm_medium'),
            'utm_campaign'=> request()->query('utm_campaign'),
            'referrer'    => request()->headers->get('referer'),
            'ip'          => request()->ip(),
            'user_agent'  => request()->userAgent(),
        ]);


        $savedName  = $data['nombre'];
        $savedFolio = 'HDV-' . strtoupper(substr(md5($submission->id . 'contacto'), 0, 4)) . '-' . $submission->id;

        $this->reset();
        $this->submitted  = true;
        $this->folio      = $savedFolio;
        $this->clientName = $savedName;
    }

    public function render()
    {
        return view('livewire.forms.contact-segmented-form');
    }
}
