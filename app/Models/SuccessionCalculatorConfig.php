<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuccessionCalculatorConfig extends Model
{
    const CON_TESTAMENTO = 'con_testamento';
    const SIN_TESTAMENTO = 'sin_testamento';

    protected $fillable = [
        'scenario',
        'notarial_pct_min', 'notarial_pct_max',
        'isai_pct_min', 'isai_pct_max',
        'registro_flat_min', 'registro_flat_max',
        'avaluo_flat_min', 'avaluo_flat_max',
        'otros_flat_min', 'otros_flat_max',
        'extra_heredero_flat',
        'sin_escrituras_extra_min', 'sin_escrituras_extra_max',
        'tiempo_min_meses', 'tiempo_max_meses',
        'validated', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'notarial_pct_min' => 'float', 'notarial_pct_max' => 'float',
            'isai_pct_min' => 'float', 'isai_pct_max' => 'float',
            'validated' => 'boolean',
        ];
    }

    public static function forScenario(string $scenario): ?self
    {
        return static::where('scenario', $scenario)->first();
    }

    /**
     * Desglose de costo para un valor de inmueble dado — Fase 4 del prompt de leads del blog.
     * NUNCA inventa cifras nuevas: solo aplica los % y rangos de esta fila (editables en
     * /admin/succession-calculator). `validated` en el resultado indica si ya se confirmaron con
     * un notario real o siguen siendo un placeholder.
     *
     * @return array{items: array<array{label:string,min:float,max:float}>, total_min:float, total_max:float,
     *               tiempo_min:int, tiempo_max:int, validated:bool}
     */
    public function estimate(float $valorInmueble, int $numHerederos, bool $tieneEscrituras): array
    {
        $numHerederos = max(1, $numHerederos);
        $pct = fn($min, $max) => [round($valorInmueble * $min / 100, 2), round($valorInmueble * $max / 100, 2)];

        [$notarialMin, $notarialMax] = $pct($this->notarial_pct_min, $this->notarial_pct_max);
        [$isaiMin, $isaiMax] = $pct($this->isai_pct_min, $this->isai_pct_max);

        $items = [
            ['label' => $this->scenario === self::CON_TESTAMENTO ? 'Trámite notarial' : 'Trámite ante notario o juicio sucesorio', 'min' => $notarialMin, 'max' => $notarialMax],
            ['label' => 'ISAI (Impuesto Sobre Adquisición de Inmuebles)', 'min' => $isaiMin, 'max' => $isaiMax],
            ['label' => 'Registro Público de la Propiedad', 'min' => (float) $this->registro_flat_min, 'max' => (float) $this->registro_flat_max],
            ['label' => 'Avalúo', 'min' => (float) $this->avaluo_flat_min, 'max' => (float) $this->avaluo_flat_max],
            ['label' => 'Otros (edictos, gestoría, copias certificadas)', 'min' => (float) $this->otros_flat_min, 'max' => (float) $this->otros_flat_max],
        ];

        if ($numHerederos > 1) {
            $extra = ($numHerederos - 1) * $this->extra_heredero_flat;
            $items[] = ['label' => ($numHerederos - 1) . ' heredero(s) adicional(es)', 'min' => (float) $extra, 'max' => (float) $extra];
        }

        if (! $tieneEscrituras && ($this->sin_escrituras_extra_min > 0 || $this->sin_escrituras_extra_max > 0)) {
            $items[] = ['label' => 'Regularizar escrituras a nombre del difunto', 'min' => (float) $this->sin_escrituras_extra_min, 'max' => (float) $this->sin_escrituras_extra_max];
        }

        $totalMin = array_sum(array_column($items, 'min'));
        $totalMax = array_sum(array_column($items, 'max'));

        return [
            'items' => $items,
            'total_min' => $totalMin,
            'total_max' => $totalMax,
            'tiempo_min' => $this->tiempo_min_meses,
            'tiempo_max' => $this->tiempo_max_meses,
            'validated' => $this->validated,
        ];
    }
}
