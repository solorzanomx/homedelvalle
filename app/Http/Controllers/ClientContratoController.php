<?php

namespace App\Http\Controllers;

use App\Actions\Contracts\GenerarContratoConfidencialidadAction;
use App\Actions\Contracts\EnviarContratoConfidencialidadAction;
use App\Models\Client;
use App\Models\GoogleSignatureRequest;
use Illuminate\Http\RedirectResponse;

class ClientContratoController extends Controller
{
    public function generar(Client $client): RedirectResponse
    {
        $this->authorize('view', $client);

        // Solo generar si no hay ningún borrador/pendiente activo
        $existing = GoogleSignatureRequest::where('contacto_id', $client->id)
            ->where('tipo', 'confidencialidad')
            ->whereIn('status', ['draft', 'pending'])
            ->latest()->first();

        if ($existing) {
            return redirect()->back()->with('error', 'Ya existe un contrato en proceso para este cliente.');
        }

        try {
            $action = app(GenerarContratoConfidencialidadAction::class);
            $action->execute($client);

            return redirect()->back()->with('success', 'Contrato generado. Revísalo en Drive antes de enviarlo al cliente.');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Error al generar contrato: ' . $e->getMessage());
        }
    }

    public function enviar(GoogleSignatureRequest $signatureRequest): RedirectResponse
    {
        $client = $signatureRequest->contacto;
        $this->authorize('view', $client);

        if ($signatureRequest->status !== 'draft') {
            return redirect()->back()->with('error', 'Este contrato ya fue enviado o no está en borrador.');
        }

        try {
            $action = app(EnviarContratoConfidencialidadAction::class);
            $action->execute($signatureRequest);

            return redirect()->back()->with('success', 'Contrato enviado. El cliente recibirá un correo para firmarlo.');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Error al enviar contrato: ' . $e->getMessage());
        }
    }
}
