<?php

namespace App\Observers;

use App\Models\Captacion;

class CaptacionObserver
{
    /**
     * Mismo patrón que OperationObserver. Red de seguridad: CaptacionIntakeService
     * ya asigna valores válidos de INTENTS/SOURCES, pero nada protegía a un
     * camino de alta futuro de colar un valor fuera de esas listas.
     */
    public function creating(Captacion $captacion): void
    {
        if ($captacion->intent && !array_key_exists($captacion->intent, Captacion::INTENTS)) {
            throw new \InvalidArgumentException("Captacion::intent invalido: '{$captacion->intent}'.");
        }

        if ($captacion->source && !array_key_exists($captacion->source, Captacion::SOURCES)) {
            throw new \InvalidArgumentException("Captacion::source invalido: '{$captacion->source}'.");
        }
    }
}
