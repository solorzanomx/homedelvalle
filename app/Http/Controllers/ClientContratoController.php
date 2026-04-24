<?php

namespace App\Http\Controllers;

use App\Actions\Contracts\ConfirmarFirmaManualAction;
use App\Actions\Contracts\EnviarContratoConfidencialidadAction;
use App\Actions\Contracts\GenerarContratoConfidencialidadAction;
use App\Models\Client;
use App\Models\GoogleSignatureRequest;
use Illuminate\Http\RedirectResponse;

class ClientContratoController extends Controller
{
    public function generar(Client $client): RedirectResponse
    {
        $this->authorize('view', $client);

        $existing = GoogleSignatureRequest::where('contacto_id', $client->id)
            ->where('tipo', 'confidencialidad')
            ->whereIn('status', ['draft', 'pending'])
            ->latest()->first();

        if ($existing) {
            return redirect()->back()->with('error', 'Ya existe un contrato en proceso para este cliente.');
        }

        try {
            app(GenerarContratoConfidencialidadAction::class)->execute($client);
            return redirect()->back()->with('success', 'Contrato generado. Revísalo en Drive antes de enviarlo al cliente.');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Error al generar contrato: ' . $e->getMessage());
        }
    }

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
