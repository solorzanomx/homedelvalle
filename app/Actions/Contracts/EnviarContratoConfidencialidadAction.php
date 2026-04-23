<?php

namespace App\Actions\Contracts;

use App\Models\Client;
use App\Models\GoogleSignatureRequest;
use App\Services\GoogleDocsService;
use App\Services\GoogleESignatureService;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class EnviarContratoConfidencialidadAction
{
    public function __construct(
        private GoogleDocsService       $docs,
        private GoogleESignatureService $eSignature,
        private WhatsAppService         $whatsapp,
    ) {}

    public function execute(Client $client): GoogleSignatureRequest
    {
        $templateId   = config('services.google_drive.template_confidencialidad');
        $folderId     = config('services.google_drive.folder_id');
        $documentName = 'Confidencialidad — ' . $client->name . ' — ' . now()->format('d/m/Y');

        // 1. Copiar template en Drive y reemplazar placeholders
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

        // 2. Crear registro en BD
        $record = GoogleSignatureRequest::create([
            'file_id'       => $fileId,
            'token'         => Str::uuid()->toString(),
            'tipo'          => 'confidencialidad',
            'contacto_id'   => $client->id,
            'status'        => 'pending',
            'document_name' => $documentName,
            'signers'       => [
                ['name' => $client->name, 'email' => $client->email, 'role' => 'signer'],
            ],
        ]);

        // 3. Enviar solicitud de firma electrónica
        try {
            $result = $this->eSignature->requestSignature($fileId, $record->signers);

            $record->update([
                'signature_request_id' => $result['signature_request_id'],
                'google_response'      => $result['raw'] ?? $result,
            ]);
        } catch (\Throwable $e) {
            Log::error('EnviarContratoConfidencialidad: error en eSignature', [
                'client_id' => $client->id,
                'file_id'   => $fileId,
                'error'     => $e->getMessage(),
            ]);
        }

        // 4. Notificar al cliente por WhatsApp
        $phone = $client->whatsapp ?? $client->phone ?? null;
        if ($phone) {
            try {
                $this->whatsapp->send(
                    $phone,
                    "Hola {$client->name}, te hemos enviado un correo para firmar tu contrato de confidencialidad. " .
                    'Por favor revísalo para continuar con el proceso de valuación.'
                );
            } catch (\Throwable $e) {
                Log::warning('EnviarContratoConfidencialidad: no se pudo enviar WhatsApp', [
                    'client_id' => $client->id,
                    'error'     => $e->getMessage(),
                ]);
            }
        }

        return $record->fresh();
    }
}
