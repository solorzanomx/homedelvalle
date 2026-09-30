<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Filtra los patrones de teléfono OBVIAMENTE falsos que ya pasaban el regex de "10 dígitos"
 * (1111111111, 1234567890…) — hallazgo real 2026-09-30: leads del blog con "1111111111" como
 * WhatsApp, imposibles de contactar. No verifica que el número sea real de verdad (eso requeriría
 * mandar un SMS/llamada) — solo descarta los casos evidentes de alguien tecleando cualquier cosa
 * para ver el resultado sin dejar contacto real.
 */
class RealisticMexicanPhone implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $digits = preg_replace('/\D/', '', (string) $value);
        $digits = substr($digits, -10);

        // El formato (10 dígitos) ya lo valida el regex principal del campo — esta regla solo
        // descarta patrones, así que si no llegaron 10 dígitos limpios no opina.
        if (strlen($digits) !== 10) {
            return;
        }

        $esMismoDigitoRepetido = preg_match('/^(\d)\1{9}$/', $digits) === 1;

        if ($esMismoDigitoRepetido || $this->esSecuencia($digits)) {
            $fail('Ese número no parece un WhatsApp real — revísalo antes de enviar.');
        }
    }

    /**
     * Secuencia consecutiva en cualquier rotación (0123456789, 1234567890, …9876543210,
     * 0987654321…) — no solo la que empieza justo en 0 o en 9. "1234567890" es el patrón más común
     * de todos al teclear el teclado numérico en orden, y no es igual, carácter por carácter, a
     * "0123456789" (bug real encontrado con el propio test de esta regla: pasaba sin rechazarse).
     */
    private function esSecuencia(string $digits): bool
    {
        $ascendente = true;
        $descendente = true;

        for ($i = 1; $i < strlen($digits); $i++) {
            $anterior = (int) $digits[$i - 1];
            $actual = (int) $digits[$i];
            $ascendente = $ascendente && $actual === ($anterior + 1) % 10;
            $descendente = $descendente && $actual === ($anterior + 9) % 10;
        }

        return $ascendente || $descendente;
    }
}
