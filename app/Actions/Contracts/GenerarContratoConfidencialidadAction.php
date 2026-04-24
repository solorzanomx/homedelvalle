<?php

namespace App\Actions\Contracts;

use App\Models\Client;
use App\Models\GoogleSignatureRequest;
use App\Models\LegalDocument;
use App\Services\GoogleDriveService;
use Illuminate\Support\Str;
use RuntimeException;

class GenerarContratoConfidencialidadAction
{
    public function __construct(
        private GoogleDriveService $drive,
    ) {}

    public function execute(Client $client): GoogleSignatureRequest
    {
        $parentFolder = config('services.google_drive.folder_id');
        $documentName = 'Contrato de Confidencialidad — ' . $client->name;

        // 1. Obtener template desde Legal > Documentos
        $template = LegalDocument::where('slug', 'contrato-confidencialidad')
            ->with('currentVersion')
            ->first();

        if (!$template || !$template->currentVersion) {
            throw new RuntimeException(
                'No se encontró el template del contrato de confidencialidad. ' .
                'Créalo en Legal > Documentos con el slug "contrato-confidencialidad".'
            );
        }

        // 2. Reemplazar variables en el contenido HTML
        $blank = '_______________';
        $html  = strtr($template->currentVersion->content, [
            '{{fecha}}'              => now()->locale('es')->isoFormat('D [de] MMMM [de] YYYY'),
            '{{nombre}}'             => $client->name,
            '{{curp}}'               => $client->curp    ?? $blank,
            '{{rfc}}'                => $client->rfc     ?? $blank,
            '{{domicilio}}'          => $client->address ?? $blank,
            '{{telefono}}'           => $client->phone   ?? $blank,
            '{{correo}}'             => $client->email   ?? $blank,
            '{{direccion_inmueble}}' => $blank,
            '{{colonia}}'            => $blank,
        ]);

        // 3. Crear carpeta del cliente en Drive
        $folderId = $this->drive->createFolder('docs-' . $client->name, $parentFolder);

        // 4. Crear Google Doc desde el HTML
        $fileId = $this->drive->createDocFromHtml($documentName, $html, $folderId);

        // 5. Guardar como borrador para revisión del admin
        return GoogleSignatureRequest::create([
            'file_id'         => $fileId,
            'drive_folder_id' => $folderId,
            'token'           => Str::uuid()->toString(),
            'tipo'            => 'confidencialidad',
            'contacto_id'     => $client->id,
            'status'          => 'draft',
            'document_name'   => $documentName,
            'signers'         => [
                ['name' => $client->name, 'email' => $client->email, 'role' => 'signer'],
            ],
        ]);
    }
}
