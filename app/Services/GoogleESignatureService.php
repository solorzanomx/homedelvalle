<?php

namespace App\Services;

use Google\Client as GoogleClient;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Gestiona solicitudes de firma electrónica vía Google Workspace eSignature API.
 *
 * PREREQUISITOS MANUALES:
 *  1. En Google Cloud Console: habilitar "Google Workspace eSignature API"
 *  2. En Google Workspace Admin > Seguridad > Acceso a datos y API de terceros:
 *     Agregar Client ID del Service Account con scopes:
 *       https://www.googleapis.com/auth/drive
 *       https://www.googleapis.com/auth/drive.file
 *  3. El dominio debe tener eSignature habilitado en Google Workspace
 *
 * API Reference: https://developers.google.com/workspace/esignature/reference/rest
 */
class GoogleESignatureService
{
    private const API_BASE = 'https://esignature.googleapis.com/v1';

    private GoogleDriveService $driveService;

    public function __construct(GoogleDriveService $driveService)
    {
        $this->driveService = $driveService;
    }

    // ── Auth ─────────────────────────────────────────────────────────────────

    /**
     * Construye el cliente HTTP con Bearer token del Service Account.
     * El SA debe tener domain-wide delegation y el admin_email configurado.
     */
    private function http(): PendingRequest
    {
        $client = $this->driveService->getDriveClient();
        $client->addScope('https://www.googleapis.com/auth/drive');

        $token = $client->fetchAccessTokenWithAssertion();

        if (isset($token['error'])) {
            throw new RuntimeException(
                "Error obteniendo token de Google: {$token['error_description']}"
            );
        }

        return Http::withToken($token['access_token'])
            ->acceptJson()
            ->contentType('application/json');
    }

    // ── Signature request ─────────────────────────────────────────────────────

    /**
     * Inicia una solicitud de firma electrónica para un archivo en Google Drive.
     *
     * @param  string  $fileId   ID del archivo en Drive (debe ser PDF o Google Doc)
     * @param  array   $signers  [['name' => '', 'email' => '', 'role' => 'signer']]
     * @return array   ['signature_request_id' => '', 'status' => 'SIGNATURE_REQUEST_SENT']
     */
    public function requestSignature(string $fileId, array $signers): array
    {
        $requesters = array_map(fn($s) => [
            'email'        => $s['email'],
            'displayName'  => $s['name'] ?? '',
            'role'         => strtoupper($s['role'] ?? 'SIGNER'), // SIGNER | APPROVER
        ], $signers);

        $payload = [
            'documentFiles' => [
                [
                    'fileId' => $fileId,
                ]
            ],
            'signers' => $requesters,
            // Título opcional que aparecerá en el correo de invitación
            'subject'        => 'Por favor firma el documento adjunto',
            'message'        => 'Te enviamos este documento para tu firma electrónica.',
        ];

        $response = $this->http()
            ->post(self::API_BASE . '/requests', $payload);

        if ($response->failed()) {
            Log::error('GoogleESignature: error al crear solicitud', [
                'file_id'  => $fileId,
                'status'   => $response->status(),
                'body'     => $response->body(),
            ]);
            throw new RuntimeException(
                "Google eSignature API error {$response->status()}: {$response->body()}"
            );
        }

        $data = $response->json();

        Log::info('GoogleESignature: solicitud creada', [
            'file_id'              => $fileId,
            'signature_request_id' => $data['name'] ?? null,
            'status'               => $data['state'] ?? null,
        ]);

        return [
            'signature_request_id' => $data['name'] ?? null,  // ej: "requests/abc123"
            'status'               => $data['state'] ?? 'SIGNATURE_REQUEST_SENT',
            'raw'                  => $data,
        ];
    }

    // ── Status ────────────────────────────────────────────────────────────────

    /**
     * Consulta el estado actual de una solicitud de firma.
     *
     * @param  string  $signatureRequestId  El valor 'name' retornado por requestSignature
     *                                      ej: "requests/abc123"
     * @return array  ['status' => 'COMPLETED|PENDING|DECLINED', 'signers' => [...]]
     */
    public function getSignatureStatus(string $signatureRequestId): array
    {
        // Normalizar: si viene sin prefijo 'requests/' se lo añadimos
        if (!str_starts_with($signatureRequestId, 'requests/')) {
            $signatureRequestId = "requests/{$signatureRequestId}";
        }

        $response = $this->http()
            ->get(self::API_BASE . '/' . $signatureRequestId);

        if ($response->failed()) {
            Log::error('GoogleESignature: error al consultar estado', [
                'request_id' => $signatureRequestId,
                'status'     => $response->status(),
                'body'       => $response->body(),
            ]);
            throw new RuntimeException(
                "Google eSignature status error {$response->status()}: {$response->body()}"
            );
        }

        $data = $response->json();

        // Mapear estado de Google a nuestros estados internos
        $stateMap = [
            'SIGNATURE_REQUEST_SENT'     => 'pending',
            'SIGNATURE_REQUEST_SIGNED'   => 'completed',
            'SIGNATURE_REQUEST_DECLINED' => 'declined',
            'SIGNATURE_REQUEST_EXPIRED'  => 'declined',
        ];

        $googleState  = $data['state'] ?? 'UNKNOWN';
        $internalState = $stateMap[$googleState] ?? 'pending';

        return [
            'status'       => $internalState,
            'google_state' => $googleState,
            'signers'      => $data['signers'] ?? [],
            'raw'          => $data,
        ];
    }

    // ── Cancel ────────────────────────────────────────────────────────────────

    /**
     * Cancela una solicitud de firma en curso.
     */
    public function cancelRequest(string $signatureRequestId): bool
    {
        if (!str_starts_with($signatureRequestId, 'requests/')) {
            $signatureRequestId = "requests/{$signatureRequestId}";
        }

        $response = $this->http()
            ->post(self::API_BASE . '/' . $signatureRequestId . ':cancel', []);

        return $response->successful();
    }
}
