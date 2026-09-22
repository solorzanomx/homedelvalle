<?php

namespace App\Livewire\Portal;

use App\Models\Captacion;
use App\Models\Document;
use App\Models\Notification;
use App\Models\Property;
use App\Services\ClientPortalService;
use App\Services\IdDocumentAIVerificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Livewire Document Upload + List — Portal del Cliente
 *
 * Sube directo vía Livewire (WithFileUploads) — sin POST clásico ni
 * recarga de página. Si allowedCategories trae una sola categoría, se
 * comporta como una "casilla" fija (sin selector, sube directo a esa
 * categoría) — así se usa para INE frente/reverso, comprobante de
 * ingresos según el tipo elegido, etc. Con varias categorías se comporta
 * como antes: selector + lista.
 */
class DocumentUploader extends Component
{
    use WithFileUploads;

    public ?int  $rentalProcessId    = null;
    public array $allowedCategories  = [];
    public bool  $showForm           = false;
    public string $successMsg        = '';
    public string $errorMsg          = '';
    public array  $documents         = [];

    public $file = null;
    public string $category = '';
    public string $label    = '';
    public bool  $uploading = false;

    public function mount(?int $rentalProcessId = null, array $allowedCategories = [])
    {
        $this->rentalProcessId  = $rentalProcessId;
        $this->allowedCategories = $allowedCategories;

        if (count($allowedCategories) === 1) {
            $this->category = $allowedCategories[0];
        }

        if (session('success')) {
            $this->successMsg = session('success');
        }

        $this->loadDocuments();
    }

    /** En modo casilla única no hay botón "Subir" — se sube en cuanto se elige el archivo. */
    public function updatedFile(): void
    {
        if ($this->isSingleSlot() && $this->file) {
            $this->upload();
        }
    }

    public function isSingleSlot(): bool
    {
        return count($this->allowedCategories) === 1;
    }

    public function loadDocuments(): void
    {
        $client = $this->getClient();
        if (! $client) { $this->documents = []; return; }

        $query = Document::query();

        if ($this->rentalProcessId) {
            $query->where('rental_process_id', $this->rentalProcessId)
                  ->where('client_id', $client->id);
        } else {
            $query->where('client_id', $client->id)
                  ->whereNull('rental_process_id')
                  ->whereNull('captacion_id');
        }

        if ($this->isSingleSlot()) {
            $query->where('category', $this->allowedCategories[0]);
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
                'isImage'     => in_array($d->mime_type, ['image/jpeg', 'image/jpg', 'image/png']),
                'thumbUrl'    => in_array($d->mime_type, ['image/jpeg', 'image/jpg', 'image/png']) && !str_starts_with($d->file_path, '/')
                                    ? \Illuminate\Support\Facades\Storage::disk('public')->url($d->file_path)
                                    : null,
                'aiStatus'      => $d->ai_verification_status,
                'aiStatusLabel' => $d->ai_verification_status_label,
                'aiNotes'       => $d->ai_verification_notes,
            ])
            ->toArray();
    }

    public function upload(): void
    {
        $this->uploading = true;

        $rules = [
            'file' => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx',
        ];
        if (! $this->isSingleSlot()) {
            $rules['category'] = 'required|string';
        }
        $this->validate($rules);

        $client = $this->getClient();
        if (! $client) {
            $this->errorMsg = 'No se encontró tu cuenta de cliente.';
            $this->uploading = false;
            return;
        }

        if (! $this->category) {
            $this->errorMsg = 'Selecciona una categoría.';
            $this->uploading = false;
            return;
        }

        $path  = $this->file->store('documents/client-' . $client->id, 'public');
        $label = $this->label ?: (Document::CATEGORIES[$this->category] ?? $this->file->getClientOriginalName());

        $captacion = Captacion::where('client_id', $client->id)->with('property')->latest()->first();
        $property  = $captacion?->property ?? Property::where('client_id', $client->id)->latest()->first();

        $document = Document::create([
            'client_id'         => $client->id,
            'property_id'       => $property?->id,
            'rental_process_id' => $this->rentalProcessId,
            'uploaded_by'       => Auth::id(),
            'category'          => $this->category,
            'label'             => $label,
            'file_path'         => $path,
            'file_name'         => $this->file->getClientOriginalName(),
            'mime_type'         => $this->file->getMimeType(),
            'file_size'         => $this->file->getSize(),
            'status'            => 'received',
        ]);

        $this->notifyBroker($client, $document);

        if (app(IdDocumentAIVerificationService::class)->shouldVerify($document)) {
            app(IdDocumentAIVerificationService::class)->verify($document, $client);
        }

        $this->reset(['file', 'label']);
        if (! $this->isSingleSlot()) {
            $this->category = '';
        }
        $this->showForm = false;
        $this->uploading = false;
        $this->successMsg = 'Documento subido correctamente.';
        $this->loadDocuments();
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

    private function notifyBroker($client, Document $document): void
    {
        $assignedUserId = $client->assigned_user_id;
        if (! $assignedUserId) return;

        try {
            Notification::create([
                'user_id' => $assignedUserId,
                'type'    => 'system',
                'title'   => 'Documento subido',
                'body'    => "{$client->name} subió un documento: {$document->label}.",
                'data'    => ['url' => route('clients.show', $client->id), 'client_id' => $client->id, 'document_id' => $document->id],
            ]);
        } catch (\Throwable $e) {
            Log::warning('DocumentUploader: no se pudo notificar al asesor', ['error' => $e->getMessage()]);
        }
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
