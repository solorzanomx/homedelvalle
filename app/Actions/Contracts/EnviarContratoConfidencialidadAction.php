<?php

namespace App\Actions\Contracts;

use App\Models\GoogleSignatureRequest;
use App\Models\Interaction;
use Illuminate\Support\Facades\Log;

class EnviarContratoConfidencialidadAction
{
    public function execute(GoogleSignatureRequest $record): GoogleSignatureRequest
    {
        $client = $record->contacto;

        $record->update(['status' => 'pending']);

        // Registrar en el timeline del cliente
        Interaction::create([
            'client_id'   => $client->id,
            'user_id'     => auth()->id(),
            'type'        => 'note',
            'description' => 'Contrato de Confidencialidad enviado al cliente para firma. En espera de confirmación.',
        ]);

        return $record->fresh();
    }
}
