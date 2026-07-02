<?php

namespace App\Support;

/**
 * Convierte un monto en pesos a su representación en letras, formato
 * estándar de documentos legales mexicanos (ej. "UN MILLÓN DOSCIENTOS
 * CINCUENTA MIL PESOS 00/100 M.N."). Sin dependencia externa.
 */
class NumeroALetras
{
    private const UNIDADES = ['', 'un', 'dos', 'tres', 'cuatro', 'cinco', 'seis', 'siete', 'ocho', 'nueve'];
    private const DIEZ_A_DIECINUEVE = ['diez', 'once', 'doce', 'trece', 'catorce', 'quince', 'dieciséis', 'diecisiete', 'dieciocho', 'diecinueve'];
    private const DECENAS = ['', '', 'veinte', 'treinta', 'cuarenta', 'cincuenta', 'sesenta', 'setenta', 'ochenta', 'noventa'];
    private const CENTENAS = ['', 'ciento', 'doscientos', 'trescientos', 'cuatrocientos', 'quinientos', 'seiscientos', 'setecientos', 'ochocientos', 'novecientos'];

    public static function pesos(float $monto): string
    {
        $entero   = (int) floor($monto);
        $centavos = (int) round(($monto - $entero) * 100);

        $letras = self::entero($entero);
        $letras = $letras === 'un' ? 'un peso' : ($letras . ' pesos');

        return mb_strtoupper($letras) . ' ' . str_pad((string) $centavos, 2, '0', STR_PAD_LEFT) . '/100 M.N.';
    }

    public static function entero(int $n): string
    {
        if ($n === 0) {
            return 'cero';
        }
        if ($n < 0) {
            return 'menos ' . self::entero(-$n);
        }

        if ($n < 1_000_000) {
            return self::milesYCentenas($n);
        }

        $millones = intdiv($n, 1_000_000);
        $resto    = $n % 1_000_000;

        $prefijo = $millones === 1 ? 'un millón' : self::milesYCentenas($millones) . ' millones';

        // "un millón de pesos" / "dos millones de pesos" solo lleva "de" cuando
        // es un monto exacto de millones (sin miles/centenas de resto).
        return $resto > 0 ? $prefijo . ' ' . self::milesYCentenas($resto) : $prefijo . ' de';
    }

    private static function milesYCentenas(int $n): string
    {
        if ($n < 1000) {
            return self::centenas($n);
        }

        $miles = intdiv($n, 1000);
        $resto = $n % 1000;

        $prefijo = $miles === 1 ? 'mil' : self::centenas($miles) . ' mil';

        return $resto > 0 ? $prefijo . ' ' . self::centenas($resto) : $prefijo;
    }

    private static function centenas(int $n): string
    {
        if ($n === 100) {
            return 'cien';
        }
        if ($n < 100) {
            return self::decenas($n);
        }

        $centena = intdiv($n, 100);
        $resto    = $n % 100;

        $prefijo = self::CENTENAS[$centena];

        return $resto > 0 ? $prefijo . ' ' . self::decenas($resto) : $prefijo;
    }

    private static function decenas(int $n): string
    {
        if ($n < 10) {
            return self::UNIDADES[$n];
        }
        if ($n < 20) {
            return self::DIEZ_A_DIECINUEVE[$n - 10];
        }
        if ($n < 30) {
            $unidad = $n - 20;
            if ($unidad === 0) return 'veinte';
            return $unidad === 1 ? 'veintiún' : 'veinti' . self::UNIDADES[$unidad];
        }

        $decena = intdiv($n, 10);
        $unidad = $n % 10;

        $prefijo = self::DECENAS[$decena];

        return $unidad > 0 ? $prefijo . ' y ' . self::UNIDADES[$unidad] : $prefijo;
    }
}
