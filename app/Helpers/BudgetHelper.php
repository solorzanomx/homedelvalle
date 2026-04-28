<?php

namespace App\Helpers;

class BudgetHelper
{
    /**
     * Convierte un rango de presupuesto (string) a min/max en decimales
     * Ejemplo: "6m_9m" → [6000000, 9000000]
     */
    public static function convertRangeToMinMax(string $range): array
    {
        $ranges = [
            'hasta_4m'      => [0, 4000000],
            '4m_6m'         => [4000000, 6000000],
            '6m_9m'         => [6000000, 9000000],
            '9m_14m'        => [9000000, 14000000],
            '14m_plus'      => [14000000, 999999999],
            'menos_20m'     => [0, 20000000],
            '20m_50m'       => [20000000, 50000000],
            '50m_120m'      => [50000000, 120000000],
            '120m_300m'     => [120000000, 300000000],
            '300m_plus'     => [300000000, 999999999],
            'no_se'         => [null, null],
        ];

        return $ranges[$range] ?? [null, null];
    }

    /**
     * Obtiene solo el valor mínimo
     */
    public static function getMin(string $range): ?int
    {
        [$min, $max] = self::convertRangeToMinMax($range);
        return $min;
    }

    /**
     * Obtiene solo el valor máximo
     */
    public static function getMax(string $range): ?int
    {
        [$min, $max] = self::convertRangeToMinMax($range);
        return $max;
    }
}
