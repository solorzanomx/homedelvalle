<?php

namespace App\Livewire\Portal;

use App\Models\Captacion;
use App\Models\Document;
use App\Models\Notification;
use App\Models\Property;
use App\Services\AddressDocumentAIExtractionService;
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
    /** Cliente a cuyo nombre se sube (el obligado solidario, capturado por el inquilino). Se revalida en cada uso. */
    public ?int  $forClientId        = null;
    public array $allowedCategories  = [];
    public bool  $showForm           = false;
    public string $successMsg        = '';
    public string $errorMsg          = '';
    public array  $documents         = [];
    /** Cuántas casillas de la MISMA categoría se permiten (ej. 3 = "últimos 3 comprobantes"). Solo aplica en modo casilla única. */
    public int   $maxSlots           = 1;

    public $file = null;
    public string $category = '';
    public string $label    = '';
    public bool  $uploading = false;

    public function mount(?int $rentalProcessId = null, array $allowedCategories = [], int $maxSlots = 1, ?int $forClientId = null)
    {
        $this->forClientId = $forClientId;
        $this->rentalProcessId  = $rentalProcessId;
        $this->allowedCategories = $allowedCategories;
        $this->maxSlots = max(1, $maxSlots);

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
        if ($this->isSingleSlot() && $this->file && $this->remainingSlots() > 0) {
            $this->upload();
        }
    }

    public function isSingleSlot(): bool
    {
        return count($this->allowedCategories) === 1;
    }

    /** Cuántas casillas más se pueden llenar (0 = ya se llenaron todas). */
    public function remainingSlots(): int
    {
        // Un documento rechazado no ocupa casilla: el cliente debe poder volver a subirlo.
        $active = count(array_filter($this->documents, fn($d) => $d['status'] !== 'rejected'));

        return max(0, $this->maxSlots - $active);
    }

    /** Categorías de identificación donde vale la pena ofrecer cámara guiada. */
    public function isIdCategory(): bool
    {
        return $this->isSingleSlot()
            && in_array($this->allowedCategories[0], \App\Services\IdDocumentAIVerificationService::ID_CATEGORIES, true);
    }

    /** Etiqueta del lado a mostrar en el recuadro guía de la cámara. */
    public function cameraSideLabel(): string
    {
        return match ($this->allowedCategories[0] ?? '') {
            'ine_frente', 'aval_ine_frente' => 'Frente de la identificación',
            'ine_reverso', 'aval_ine_reverso' => 'Reverso de la identificación',
            'pasaporte' => 'Página de datos del pasaporte',
            default => 'Identificación',
        };
    }

    /** Proporción del recuadro guía — credencial vs. página de pasaporte. */
    public function cameraAspectRatio(): string
    {
        return ($this->allowedCategories[0] ?? '') === 'pasaporte' ? '1.42' : '1.586';
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
                'canDelete'   => in_array($d->status, ['pending', 'received', 'rejected']),
                'rejectionReason' => $d->status === 'rejected' ? $d->rejection_reason : null,
                'isImage'     => in_array($d->mime_type, ['image/jpeg', 'image/jpg', 'image/png']),
                // Miniatura por ruta AUTORIZADA (no por URL pública del disco): los archivos son privados.
                'thumbUrl'    => in_array($d->mime_type, ['image/jpeg', 'image/jpg', 'image/png'])
                                    ? route('portal.documents.preview', ['id' => $d->id, 'thumb' => 1])
                                    : null,
                'aiStatus'      => $d->ai_verification_status,
                'aiStatusLabel' => $d->ai_verification_status_label,
                'aiNotes'       => $d->ai_verification_notes,
            ])
            ->toArray();
    }

    public function upload(): void
    {
        if ($this->isSingleSlot() && $this->remainingSlots() <= 0) {
            $this->errorMsg = 'Ya subiste el máximo de documentos permitidos aquí.';
            $this->reset(['file']);
            return;
        }

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

        // Revisión de calidad ANTES de guardar: si es evidente que no se va a
        // poder leer (foto a una pantalla, muy pequeña, ilegible), se le dice
        // al cliente qué hacer en el momento.
        $quality = app(\App\Services\DocumentQualityService::class);
        $gate = $quality->gate($this->file, $this->category, $client->id);
        if ($gate['block']) {
            $this->errorMsg = $gate['block'];
            $this->reset(['file']);
            $this->uploading = false;
            return;
        }

        // OJO: el archivo temporal de Livewire vive en el MISMO disco privado; al guardarlo definitivo Livewire lo
        // MUEVE (ya no existe después), así que nombre/tamaño/tipo se leen ANTES de guardar.
        $originalName = $this->file->getClientOriginalName();
        $mimeType = $this->file->getMimeType();
        $fileSize = $this->file->getSize();

        $path  = \App\Support\SecureFiles::store($this->file, 'documents/client-' . $client->id);
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
            'file_name'         => $originalName,
            'mime_type'         => $mimeType,
            'file_size'         => $fileSize,
            'status'            => 'received',
        ]);

        $quality->record($document, $gate);

        $this->notifyBroker($client, $document);

        if (app(IdDocumentAIVerificationService::class)->shouldVerify($document)) {
            app(IdDocumentAIVerificationService::class)->verify($document, $client);
            $document->refresh();

            // Si la IA logró leer el documento, ofrece llenar el formulario
            // de Datos personales / Identificación con lo que dice la
            // identificación en vez de que el cliente lo vuelva a escribir
            // a mano (y para corregir de una vez si lo que había escrito
            // no coincidía).
            if (!empty($document->ai_extracted_data['legible'])) {
                $this->dispatch('id-data-extracted', data: $document->ai_extracted_data);
            }
        }

        if (app(AddressDocumentAIExtractionService::class)->shouldExtract($document)) {
            app(AddressDocumentAIExtractionService::class)->extract($document);
            $document->refresh();

            // Igual que con la identificación: si se pudo leer el recibo,
            // llena el formulario de Domicilio en vez de que el cliente
            // vuelva a escribir la dirección a mano.
            if (!empty($document->ai_extracted_data['legible'])) {
                $this->dispatch('address-data-extracted', data: $document->ai_extracted_data);
            }
        }

        // Estados de cuenta y nóminas: se valida por contenido (titular, periodo reciente, tipo correcto).
        if (app(\App\Services\StatementDocumentAIExtractionService::class)->shouldExtract($document)) {
            app(\App\Services\StatementDocumentAIExtractionService::class)->extract($document, $client);
            $document->refresh();
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
                  && in_array($document->status, ['pending', 'received', 'rejected']);

        if (! $canDelete) { $this->errorMsg = 'No puedes eliminar este documento.'; return; }

        \App\Support\SecureFiles::delete($document->file_path);

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
        $me = app(ClientPortalService::class)->getClientForUser(Auth::user());

        // Subida a nombre del obligado solidario: solo si quien sube es el inquilino de esa renta y el obligado sigue vigente.
        if ($this->forClientId) {
            $rental = app(\App\Services\ObligadoSolidarioService::class)->tenantMayActFor($me, $this->forClientId);

            return $rental ? \App\Models\Client::find($this->forClientId) : null;
        }

        return $me;
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
            'isIdCategory'        => $this->isIdCategory(),
            'cameraSideLabel'     => $this->cameraSideLabel(),
            'cameraAspectRatio'   => $this->cameraAspectRatio(),
            'singleCategory'      => $this->allowedCategories[0] ?? null,
            'remainingSlots'      => $this->remainingSlots(),
            'maxSlots'            => $this->maxSlots,
        ]);
    }
}
