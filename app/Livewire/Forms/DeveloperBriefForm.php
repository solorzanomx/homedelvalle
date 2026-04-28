<?php

namespace App\Livewire\Forms;

use App\Models\FormSubmission;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Livewire\WithFileUploads;

class DeveloperBriefForm extends Component
{
    use WithFileUploads;

    public array $tipo_operacion = [];
    public array $uso = [];
    public string $m2_terreno = '';
    public array $zonas = [];
    public string $presupuesto = '';
    public string $horizonte = '';
    public mixed $brief_file = null;
    public string $empresa = '';
    public string $nombre_rol = '';
    public string $email = '';
    public string $telefono = '';
    public bool $nda = false;
    public bool $aviso = false;

    public bool $submitted = false;
    public bool $isProcessing = false;
    public string $folio = '';
    public string $clientName = '';

    protected function rules(): array
    {
        return [
            'tipo_operacion' => 'required|array|min:1',
            'tipo_operacion.*' => 'in:compra_predio,compra_terminado,coinversion,asesoria',
            'uso' => 'required|array|min:1',
            'uso.*' => 'in:vertical,horizontal,mixto,comercial,oficinas,industrial',
            'm2_terreno' => 'required|in:menos_200,200_400,400_800,800_1500,1500_plus',
            'zonas' => 'required|array|min:1',
            'presupuesto' => 'required|in:menos_20m,20m_50m,50m_120m,120m_300m,300m_plus',
            'horizonte' => 'required|in:6m,6_12m,12_24m,24m_plus',
            'brief_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'empresa' => 'required|string|max:160',
            'nombre_rol' => 'required|string|max:160',
            'email' => 'required|email',
            'telefono' => 'required|string',
            'nda' => 'nullable|boolean',
            'aviso' => 'accepted',
        ];
    }

    protected array $validationAttributes = [
        'tipo_operacion' => 'tipo de operación',
        'uso' => 'uso objetivo',
        'm2_terreno' => 'rango de m²',
        'zonas' => 'zonas de interés',
        'presupuesto' => 'presupuesto',
        'horizonte' => 'horizonte de inversión',
        'brief_file' => 'archivo del brief',
        'empresa' => 'empresa',
        'nombre_rol' => 'nombre y rol',
        'email' => 'email corporativo',
        'telefono' => 'teléfono',
        'aviso' => 'aviso de privacidad',
    ];

    public function submit(): void
    {
        if ($this->isProcessing) return;
        $this->isProcessing = true;

        $data = $this->validate();

        $lockKey = 'form_submit_b2b_' . md5($data['email']);
        if (! Cache::lock($lockKey, 30)->get()) return;

        $submission = FormSubmission::create([
            'form_type'   => 'b2b',
            'source_page' => '/desarrolladores-e-inversionistas',
            'full_name'   => $data['nombre_rol'],
            'email'       => $data['email'],
            'phone'       => $data['telefono'],
            'payload'     => collect($data)->except(['nombre_rol', 'email', 'telefono', 'aviso', 'brief_file'])->toArray(),
            'lead_tag'    => 'LEAD_B2B',
            'utm_source'  => request()->query('utm_source'),
            'utm_medium'  => request()->query('utm_medium'),
            'utm_campaign'=> request()->query('utm_campaign'),
            'referrer'    => request()->headers->get('referer'),
            'ip'          => request()->ip(),
            'user_agent'  => request()->userAgent(),
        ]);

        // Handle file upload with Spatie Media Library
        if ($this->brief_file) {
            $submission->addMedia($this->brief_file)
                ->toMediaCollection('briefs');
        }


        $savedName  = $data['nombre_rol'];
        $savedFolio = 'HDV-B2B-' . strtoupper(substr(md5($submission->id . 'b2b'), 0, 4)) . '-' . $submission->id;

        $this->reset();
        $this->submitted  = true;
        $this->folio      = $savedFolio;
        $this->clientName = $savedName;
    }

    public function render()
    {
        return view('livewire.forms.developer-brief-form');
    }
}
