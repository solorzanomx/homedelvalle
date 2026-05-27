<?php

namespace App\Services;

use App\Models\Captacion;
use App\Models\User;

class CaptacionDeclineService
{
    public function __construct(protected EmailService $email) {}

    public function decline(Captacion $captacion, string $reason, User $agent): void
    {
        $captacion->loadMissing(['client', 'operation', 'createdBy']);

        // 1. Marcar la captación como declinada
        $captacion->update([
            'status'          => 'declinado',
            'declined_at'     => now(),
            'declined_reason' => $reason,
        ]);

        // 2. Cancelar la Operation asociada
        if ($captacion->operation) {
            $captacion->operation->update([
                'status'       => 'cancelled',
                'cancelled_at' => now(),
                'notes'        => ($captacion->operation->notes ? $captacion->operation->notes . "\n" : '')
                                . 'Declinado: ' . $reason,
            ]);
        }

        // 3. Email amistoso al propietario (solo si tiene email)
        if ($captacion->client?->email) {
            $this->email->sendTemplate(
                templateName: 'captacion_declined_friendly',
                to:           $captacion->client->email,
                variables: [
                    'NombrePropietario' => $captacion->client->name,
                    'NombreInmueble'    => $captacion->property_address_display,
                    'NombreAgente'      => $agent->name,
                ],
                toName:  $captacion->client->name,
                sender:  $agent,
            );
        }
    }
}
