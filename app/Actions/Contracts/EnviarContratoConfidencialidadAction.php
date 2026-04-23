<?php

namespace App\Actions\Contracts;

use App\Models\Client;
use App\Models\GoogleSignatureRequest;
use App\Services\GoogleDriveService;
use App\Services\GoogleESignatureService;
use App\Services\WhatsAppService;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class EnviarContratoConfidencialidadAction
{
    public function __construct(
        private GoogleDriveService      $drive,
        private GoogleESignatureService $eSignature,
        private WhatsAppService         $whatsapp,
    ) {}

    public function execute(Client $client): GoogleSignatureRequest
    {
        $fecha        = now()->locale('es')->isoFormat('D [de] MMMM [de] YYYY');
        $empresa      = config('app.name', 'Home del Valle');
        $documentName = 'Confidencialidad — ' . $client->name . ' — ' . now()->format('d/m/Y');

        // 1. Generar PDF con DomPDF desde blade template
        $html = view('contratos.confidencialidad', compact('client', 'fecha', 'empresa'))->render();

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        // 2. Guardar PDF temporalmente
        $tmpPath = storage_path('app/private/contratos/confidencialidad-' . $client->id . '-' . time() . '.pdf');
        @mkdir(dirname($tmpPath), 0755, true);
        file_put_contents($tmpPath, $dompdf->output());

        // 3. Subir a Google Drive
        $fileName = 'Confidencialidad-' . Str::slug($client->name) . '-' . now()->format('Ymd') . '.pdf';
        $fileId   = $this->drive->uploadPdf($tmpPath, $fileName);

        @unlink($tmpPath);

        // 4. Crear registro en BD
        $record = GoogleSignatureRequest::create([
            'file_id'         => $fileId,
            'token'           => Str::uuid()->toString(),
            'tipo'            => 'confidencialidad',
            'contacto_id'     => $client->id,
            'status'          => 'pending',
            'document_name'   => $documentName,
            'local_pdf_path'  => null,
            'signers'         => [
                ['name' => $client->name, 'email' => $client->email, 'role' => 'signer'],
            ],
        ]);

        // 5. Enviar solicitud de firma electrónica
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
            // No lanzamos excepción — el PDF ya está en Drive, puede reenviarse
        }

        // 6. Notificar al cliente por WhatsApp
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
