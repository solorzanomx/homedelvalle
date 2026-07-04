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
            'intento' => 'required|in:vender,comprar,rentar_inquilino,rentar_propietario,b2b,admin,legal,otro',
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
        $data = $this->validate(); // valida primero — si falla, isProcessing nunca se bloquea
        if ($this->isProcessing) return;
        $this->isProcessing = true;

        $tagMap = [
            'vender'            => 'LEAD_VENDEDOR',
            'comprar'           => 'LEAD_COMPRADOR',
            'rentar_inquilino'  => 'LEAD_ARRENDATARIO',
            'rentar_propietario'=> 'LEAD_PROPIETARIO_RENTA',
            'b2b'               => 'LEAD_B2B',
            'admin'             => 'LEAD_ADMIN',
            'legal'             => 'LEAD_LEGAL',
            'otro'              => 'LEAD_OTRO',
        ];

        $clientTypeMap = [
            'vender'            => 'owner',
            'comprar'           => 'buyer',
            'rentar_inquilino'  => 'renter',
            'rentar_propietario'=> 'owner',
            'b2b'               => 'investor',
            'admin'             => 'owner',
            'legal'             => 'owner',
            'otro'              => null,
        ];

        // interest_types (vocabulario real: compra|venta|renta_propietario|
        // renta_inquilino) nunca se guardaba en este form — solo client_type.
        // Sin esto, el Client resultante al convertir el lead quedaba sin
        // badges de "interés" en su ficha (bug real reportado por el
        // usuario 2026-07-04: "no se guardan con la caracteristica que
        // necesito, por ejemplo comprador" — interest_types es lo que SÍ se
        // muestra en clients/show.blade.php, client_type casi no se lee en
        // ningún lado del sistema).
        $interestTypesMap = [
            'vender'             => ['venta'],
            'comprar'            => ['compra'],
            'rentar_inquilino'   => ['renta_inquilino'],
            'rentar_propietario' => ['renta_propietario'],
        ];

        $lockKey = 'form_submit_contacto_' . md5($data['email']);
        if (! Cache::lock($lockKey, 30)->get()) return;

        // Crear FormSubmission (Lead) directamente - NO crear Client
        $submission = FormSubmission::create([
            'form_type'   => 'contacto',
            'source_page' => '/contacto',
            'full_name'   => $data['nombre'],
            'email'       => $data['email'],
            'phone'       => $data['whatsapp'],
            'payload'     => collect($data)->except(['nombre', 'email', 'whatsapp', 'aviso'])->toArray(),
            'lead_tag'    => $tagMap[$data['intento']] ?? 'LEAD_OTRO',
            'client_type' => $clientTypeMap[$data['intento']],
            'interest_types' => $interestTypesMap[$data['intento']] ?? null,
            'lead_temperature' => 'warm',
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
    // Limpia el error del campo en cuanto el usuario lo corrige
    public function updated(string $propertyName): void
    {
        if ($this->getErrorBag()->has($propertyName)) {
            $this->validateOnly($propertyName);
        }
    }


    public function render()
    {
        return view('livewire.forms.contact-segmented-form');
    }
}
