<?php

namespace App\Actions\Contracts;

use App\Models\Captacion;
use App\Models\Client;
use App\Models\Document;
use App\Models\GoogleSignatureRequest;
use App\Services\ContratoExclusivaGeneratorService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class GenerarContratoExclusivaAction
{
    public function __construct(
        private ContratoExclusivaGeneratorService $generator,
    ) {}

    public function execute(Client $client, Captacion $captacion, int $vigenciaDias = 180): GoogleSignatureRequest
    {
        $documentName = 'Acuerdo de Representación — ' . $client->name;

        $path = $this->generator->generatePdf($captacion, $vigenciaDias);

        Document::create([
            'captacion_id' => $captacion->id,
            'client_id'    => $client->id,
            'uploaded_by'  => Auth::id(),
            'category'     => 'contrato_exclusiva',
            'label'        => $documentName,
            'file_path'    => $path,
            'file_name'    => 'AR-' . str_pad((string) $captacion->id, 5, '0', STR_PAD_LEFT) . '.pdf',
            'mime_type'    => 'application/pdf',
            'file_size'    => file_exists($path) ? filesize($path) : null,
            'status'       => 'verified',
        ]);

        // Guarda como borrador para revisión del admin — el estado
        // draft/completed y la confirmación manual de firma no cambian,
        // solo el documento detrás dejó de ser un Google Doc. file_id es
        // NOT NULL + único en el esquema (antes siempre era el ID real de
        // Drive) — se rellena con un identificador local, ya no hay Drive.
        return GoogleSignatureRequest::create([
            'file_id'         => 'local-' . Str::uuid()->toString(),
            'local_pdf_path'  => $path,
            'token'           => Str::uuid()->toString(),
            'tipo'            => 'exclusiva',
            'contacto_id'     => $client->id,
            'status'          => 'draft',
            'document_name'   => $documentName,
            'signers'         => [
                ['name' => $client->name, 'email' => $client->email, 'role' => 'signer'],
            ],
        ]);
    }
}
