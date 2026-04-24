<?php

namespace App\Actions\Contracts;

use App\Models\Client;
use App\Models\GoogleSignatureRequest;
use App\Services\GoogleDocsService;
use App\Services\GoogleDriveService;
use Illuminate\Support\Str;

class GenerarContratoConfidencialidadAction
{
    public function __construct(
        private GoogleDriveService $drive,
        private GoogleDocsService  $docs,
    ) {}

    public function execute(Client $client): GoogleSignatureRequest
    {
        $templateId   = config('services.google_drive.template_confidencialidad');
        $parentFolder = config('services.google_drive.folder_id');
        $documentName = 'Contrato de Confidencialidad — ' . $client->name;

        // 1. Crear carpeta del cliente en Drive
        $folderName = 'docs-' . $client->name;
        $folderId   = $this->drive->createFolder($folderName, $parentFolder);

        // 2. Copiar template dentro de esa carpeta y reemplazar placeholders
        $fileId = $this->docs->createFromTemplate(
            templateId:   $templateId,
            documentName: $documentName,
            replacements: [
                '{{NOMBRE_CLIENTE}}' => $client->name,
                '{{EMAIL_CLIENTE}}'  => $client->email ?? '',
                '{{TELEFONO}}'       => $client->phone ?? '',
                '{{FECHA}}'          => now()->locale('es')->isoFormat('D [de] MMMM [de] YYYY'),
                '{{EMPRESA}}'        => config('app.name', 'Home del Valle'),
            ],
            folderId: $folderId,
        );

        // 3. Guardar como borrador para revisión
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
