<?php

namespace App\Livewire\Portal;

use App\Models\Document;
use App\Services\ClientPortalService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * Livewire Document List + Upload Toggle — Portal del Cliente
 * PR Portal-2
 *
 * El upload real se hace via POST al PortalDocumentController (form estándar).
 * Este componente maneja: mostrar/ocultar form, listar documentos, eliminar docs.
 */
class DocumentUploader extends Component
{
    public ?int  $rentalProcessId    = null;
    public array $allowedCategories  = [];
    public bool  $showForm           = false;
    public string $successMsg        = '';
    public string $errorMsg          = '';
    public array  $documents         = [];

    public function mount(?int $rentalProcessId = null, array $allowedCategories = [])
    {
        $this->rentalProcessId  = $rentalProcessId;
        $this->allowedCategories = $allowedCategories;

        // Mostrar flash de éxito si viene del controller
        if (session('success')) {
            $this->successMsg = session('success');
        }

        $this->loadDocuments();
    }

    public function loadDocuments(): void
    {
        $client = $this->getClient();
        if (! $client) { $this->documents = []; return; }

        $query = Document::query();

        if ($this->rentalProcessId) {
            $query->where('rental_process_id', $this->rentalProcessId);
        } else {
            $query->where('client_id', $client->id)
                  ->whereNull('rental_process_id')
                  ->whereNull('captacion_id');
        }

        $this->documents = $query
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($d) => [
                'id'          => $d->id,
                'label'       => $d->label ?? $d->file_name,
                'category'    => $d->category_label,
                'status'      => $d->status,
                'statusLabel' => $d->status_label,
                'size'        => $d->file_size ? round($d->file_size / 1024) . ' KB' : null,
                'date'        => $d->created_at->format('d/m/Y'),
                'canDelete'   => in_array($d->status, ['pending', 'received']),
            ])
            ->toArray();
    }

    public function deleteDocument(int $id): void
    {
        $client   = $this->getClient();
        $document = Document::find($id);

        if (! $document || ! $client) return;

        $canDelete = $document->client_id === $client->id
                  && in_array($document->status, ['pending', 'received']);

        if (! $canDelete) { $this->errorMsg = 'No puedes eliminar este documento.'; return; }

        if ($document->file_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($document->file_path)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();
        $this->loadDocuments();
        $this->successMsg = 'Documento eliminado.';
    }

    public function clearMessages(): void
    {
        $this->successMsg = '';
        $this->errorMsg   = '';
    }

    private function getClient(): ?\App\Models\Client
    {
        return app(ClientPortalService::class)->getClientForUser(Auth::user());
    }

    public function getAvailableCategoriesProperty(): array
    {
        $all = Document::CATEGORIES;
        if (empty($this->allowedCategories)) return $all;
        return array_intersect_key($all, array_flip($this->allowedCategories));
    }

    public function render()
    {
        return view('livewire.portal.document-uploader', [
            'availableCategories' => $this->getAvailableCategoriesProperty(),
        ]);
    }
}
