<?php

namespace App\Livewire\Admin;

use App\Models\Client;
use App\Models\FormSubmission;
use Livewire\Component;
use Livewire\WithPagination;

class FormSubmissionsTable extends Component
{
    use WithPagination;

    /**
     * Sin esto, Livewire usa su propia vista 'livewire::tailwind' (con SVGs
     * y clases de Tailwind que este proyecto no compila desde el paquete
     * vendor) — el ícono de flecha se ve gigante sin estilo. Usamos la vista
     * de paginación ya publicada del proyecto (estilos inline, no depende
     * de clases Tailwind compiladas).
     */
    public function paginationView(): string
    {
        return 'pagination::tailwind';
    }

    public string $search   = '';
    public string $type     = '';
    public string $status   = '';
    public array  $selected = [];
    public bool   $selectAll = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'type'   => ['except' => ''],
        'status' => ['except' => ''],
    ];

    public function updatingSearch(): void  { $this->resetPage(); }
    public function updatingType(): void    { $this->resetPage(); }
    public function updatingStatus(): void  { $this->resetPage(); }

    public function updatedSelectAll(bool $value): void
    {
        $this->selected = $value
            ? $this->getQuery()->pluck('id')->map(fn($id) => (string) $id)->toArray()
            : [];
    }

    public function delete(int $id): void
    {
        try {
            FormSubmission::findOrFail($id)->delete();
        } catch (\Throwable) {
            \DB::table('form_submissions')->where('id', $id)->delete();
        }
        $this->selected = array_filter($this->selected, fn($s) => (int)$s !== $id);
        session()->flash('success', 'Lead eliminado');
    }

    public function bulkDelete(): void
    {
        if (empty($this->selected)) return;
        \DB::table('form_submissions')->whereIn('id', $this->selected)->delete();
        $count = count($this->selected);
        $this->selected  = [];
        $this->selectAll = false;
        session()->flash('success', "{$count} leads eliminados");
    }

    /**
     * Delegado a LeadConversionService (compartido con Admin\FormSubmissionController::convertToClient)
     * desde el hallazgo real 2026-10-01: esta versión no "adoptaba" leads/visitas duplicadas del
     * mismo contacto ni detectaba la propiedad de interés — un cliente nuevo se convertía sin
     * rastro de su historial previo como lead.
     */
    public function convertToClient(int $id): void
    {
        $submission = FormSubmission::findOrFail($id);

        if ($submission->client_id) {
            session()->flash('success', 'Este lead ya tiene un cliente asociado.');
            return;
        }

        $result = app(\App\Services\LeadConversionService::class)->convert($submission);
        $client = $result['client'];

        if (! $result['was_existing']) {
            try {
                app(\App\Services\AutomationEngine::class)->processNewClient($client);
            } catch (\Throwable $e) {
                \Log::warning('convertToClient: processNewClient falló', ['error' => $e->getMessage()]);
            }
        }

        $msg = $result['was_existing']
            ? "Lead vinculado al cliente existente «{$client->name}»."
            : "Cliente «{$client->name}» creado exitosamente.";
        if ($result['reparented_leads'] > 0 || $result['reparented_interactions'] > 0) {
            $msg .= ' Se recuperó su historial de ' . $result['reparented_leads'] . ' lead(s) y '
                . $result['reparented_interactions'] . ' interacción(es) previas.';
        }

        // Inquilino con propiedad de interés detectada — directo al trato de renta prellenado.
        if ($result['property_id'] && in_array('renta_inquilino', $client->interest_types ?? [], true)) {
            session()->flash('success', $msg . ' Ya puedes crear el trato de renta del inmueble que le interesó.');
            $this->redirect(route('rentals.create') . '?' . http_build_query([
                'property' => $result['property_id'],
                'owner'    => $result['owner_client_id'],
                'tenant'   => $client->id,
            ]), navigate: false);
            return;
        }

        session()->flash('success', $msg);
    }

    private function getQuery()
    {
        return FormSubmission::query()
            ->when($this->search, fn($q) => $q->where(fn($q2) =>
                $q2->where('full_name', 'like', "%{$this->search}%")
                   ->orWhere('email',    'like', "%{$this->search}%")
                   ->orWhere('phone',    'like', "%{$this->search}%")
            ))
            ->when($this->type === 'brokers', fn($q) => $q->where('lead_tag', 'LEAD_BROKER'))
            ->when($this->type && $this->type !== 'brokers', fn($q) => $q->where('form_type', $this->type))
            ->when($this->status, fn($q) => $q->where('status',    $this->status))
            ->latest();
    }

    public function render()
    {
        $submissions = $this->getQuery()->paginate(25);

        // Hallazgo real 2026-10-02: Livewire::originalPath() (lo que usa el resolver de paginación
        // de Livewire por default) devuelve request()->path() SIN el "/" inicial — Paginator::url()
        // lo concatena tal cual, así que el href queda "admin/form-submissions?page=2" (relativo).
        // Con una ruta de un solo segmento el navegador lo resuelve bien por accidente; con el
        // prefijo /admin (dos segmentos) lo resuelve relativo al directorio actual y duplica
        // "admin" → 404. withPath() fuerza una URL absoluta correcta, reusando el path real tanto
        // en la carga inicial como en los re-renders de Livewire (paginar, buscar, etc.).
        $submissions->withPath(url(\Livewire\Livewire::originalPath()));

        $counts = [
            'total'     => FormSubmission::count(),
            'unseen'    => FormSubmission::whereNull('seen_at')->count(),
            'vendedor'  => FormSubmission::where('form_type', 'vendedor')->count(),
            'predio'    => FormSubmission::where('form_type', 'vendedor_predio')->count(),
            'comprador' => FormSubmission::where('form_type', 'comprador')->count(),
            'b2b'       => FormSubmission::where('form_type', 'b2b')->count(),
            'contacto'  => FormSubmission::where('form_type', 'contacto')->count(),
            'easybroker' => FormSubmission::where('form_type', 'easybroker')->count(),
            'inmuebles24' => FormSubmission::where('form_type', 'inmuebles24')->count(),
            'brokers'    => FormSubmission::where('lead_tag', 'LEAD_BROKER')->count(),
        ];

        return view('livewire.admin.form-submissions-table', [
            'submissions' => $submissions,
            'counts'      => $counts,
        ]);
    }
}
