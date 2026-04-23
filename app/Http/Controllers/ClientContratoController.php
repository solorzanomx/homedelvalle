<?php

namespace App\Http\Controllers;

use App\Actions\Contracts\EnviarContratoConfidencialidadAction;
use App\Models\Client;
use Illuminate\Http\RedirectResponse;

class ClientContratoController extends Controller
{
    public function enviarConfidencialidad(Client $client): RedirectResponse
    {
        $this->authorize('view', $client);

        try {
            $action = app(EnviarContratoConfidencialidadAction::class);
            $action->execute($client);

            return redirect()->back()->with('success', 'Contrato de confidencialidad enviado. El cliente recibirá un correo para firmarlo.');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Error al enviar contrato: ' . $e->getMessage());
        }
    }
}
