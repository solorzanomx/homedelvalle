<?php

namespace App\Http\Controllers;

use App\Actions\Contracts\ConfirmarFirmaManualAction;
use App\Actions\Contracts\EnviarContratoConfidencialidadAction;
use App\Models\GoogleSignatureRequest;
use Illuminate\Http\RedirectResponse;

/**
 * Solo queda para avanzar las solicitudes de firma de Confidencialidad por
 * Google Docs que ya estaban en curso antes de 2026-09-21 (draft/pending) —
 * la creación de nuevas (generar()) se retiró: ahora se acepta por clic
 * dentro del portal (ver EnsurePortalLegalAcceptance / config/portal.php).
 */
class ClientContratoController extends Controller
{
    public function enviar(GoogleSignatureRequest $signatureRequest): RedirectResponse
    {
        $this->authorize('view', $signatureRequest->contacto);

        if ($signatureRequest->status !== 'draft') {
            return redirect()->back()->with('error', 'Este contrato no está en borrador.');
        }

        try {
            app(EnviarContratoConfidencialidadAction::class)->execute($signatureRequest);
            return redirect()->back()->with('success', 'Cliente notificado. Cuando confirmes la firma, haz clic en "Confirmar firma recibida".');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function confirmar(GoogleSignatureRequest $signatureRequest): RedirectResponse
    {
        $this->authorize('view', $signatureRequest->contacto);

        if ($signatureRequest->status !== 'pending') {
            return redirect()->back()->with('error', 'El contrato no está en estado pendiente.');
        }

        try {
            app(ConfirmarFirmaManualAction::class)->execute($signatureRequest);
            return redirect()->back()->with('success', 'Firma confirmada. Se ha creado el acceso al portal y notificado al cliente.');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Error al confirmar firma: ' . $e->getMessage());
        }
    }
}
