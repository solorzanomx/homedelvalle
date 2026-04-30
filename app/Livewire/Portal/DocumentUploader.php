<?php

namespace App\Livewire\Portal;

use App\Models\Document;
use App\Services\ClientPortalService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Livewire Document Uploader — Portal del Cliente
 * PR Portal-2
 *
 * Embeddable en cualquier vista del portal.
 * Props:
 *   - rentalProcessId: int|null  → contexto de renta
 *   - captacionId: int|null      → contexto de captación (no usado aquí)
 *   - categories: array|null     → filtrar categorías disponibles
 */
class DocumentUploader extends Component
{
    use WithFileUploads;

    // ── Props (configurables desde el padre) ──────────────────────────────────
    public ?int $rentalProcessId = null;
    public array $allowedCategories = [];

    // ── Estado del formulario ─────────────────────────────────────────────────
    public $file;
    public string $category = '';
    public string $label    = '';

    // ── UI state ──────────────────────────────────────────────────────────────
    public bool   $showForm  = false;
    public bool   $uploading = false;
    public string $successMsg = '';
    public string $errorMsg   = '';

    // ── Documentos cargados (para mostrar en lista) ────────────────────────────
    public array $documents = [];

    public function mount(?int $rentalProcessId = null, array $allowedCategories = [])
    {
        $this->rentalProcessId   = $rentalProcessId;
        $this->allowedCategories = $allowedCategories;
        $this->loadDocuments();
    }

    public function loadDocuments(): void
    {
        $client = $this->getClient();
        if (! $client) {
            $this->documents = [];
            return;
        }

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
                'id'         => $d->id,
                'label'      => $d->label ?? $d->file_name,
                'category'   => $d->category_label,
                'status'     => $d->status,
                'statusLabel'=> $d->status_label,
                'size'       => $d->file_size ? round($d->file_size / 1024) . ' KB' : null,
                'date'       => $d->created_at->format('d/m/Y'),
                'canDelete'  => in_array($d->status, ['pending', 'received']),
            ])
            ->toArray();
    }

    public function upload(): void
    {
        $this->validate([
            'file'     => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx',
            'category' => 'required|string',
            'label'    => 'required|string|max:100',
        ], [
            'file.required'     => 'Selecciona un archivo.',
            'file.max'          => 'El archivo no puede superar 10 MB.',
            'file.mimes'        => 'Solo se aceptan PDF, JPG, PNG, DOC y DOCX.',
            'category.required' => 'Selecciona una categoría.',
            'label.required'    => 'Escribe un nombre para el documento.',
        ]);

        $client = $this->getClient();
        if (! $client) {
            $this->errorMsg = 'No se pudo identificar tu cuenta.';
            return;
        }

        $this->uploading = true;

        try {
            $path = $this->file->store(
                'documents/client-' . $client->id,
                'public'
            );

            Document::create([
                'client_id'         => $client->id,
                'rental_process_id' => $this->rentalProcessId,
                'uploaded_by'       => Auth::id(),
                'category'          => $this->category,
                'label'             => $this->label,
                'file_path'         => $path,
                'file_name'         => $this->file->getClientOriginalName(),
                'mime_type'         => $this->file->getMimeType(),
                'file_size'         => $this->file->getSize(),
                'status'            => 'received',
            ]);

            $this->reset('file', 'category', 'label', 'showForm', 'errorMsg');
            $this->successMsg = '¡Documento subido correctamente!';
            $this->loadDocuments();

            // Desaparecer éxito después de 4s
            $this->dispatch('doc-uploaded');

        } catch (\Throwable $e) {
            $this->errorMsg = 'Ocurrió un error al subir el archivo. Intenta de nuevo.';
        } finally {
            $this->uploading = false;
        }
    }

    public function deleteDocument(int $id): void
    {
        $client   = $this->getClient();
        $document = Document::find($id);

        if (! $document || ! $client) return;

        // Verificar pertenencia
        $canDelete = $document->client_id === $client->id
                  && in_array($document->status, ['pending', 'received']);

        if (! $canDelete) {
            $this->errorMsg = 'No puedes eliminar este documento.';
            return;
        }

        // Eliminar archivo físico
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

        if (empty($this->allowedCategories)) {
            return $all;
        }

        return array_intersect_key($all, array_flip($this->allowedCategories));
    }

    public function render()
    {
        return view('livewire.portal.document-uploader', [
            'availableCategories' => $this->getAvailableCategoriesProperty(),
        ]);
    }
}
