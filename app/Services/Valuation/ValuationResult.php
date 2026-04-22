<?php

namespace App\Services\Valuation;

use App\Models\MarketPriceSnapshot;
use App\Models\PropertyValuation;

class ValuationResult
{
    public function __construct(
        public readonly float   $basePrice,
        public readonly float   $adjustedPrice,
        public readonly int     $totalLow,
        public readonly int     $totalMid,
        public readonly int     $totalHigh,
        public readonly int     $suggested,
        public readonly string  $diagnosis,
        public readonly string  $confidence,
        public readonly array   $adjustments,
        public readonly ?MarketPriceSnapshot $snapshot = null,
    ) {}

    public static function insufficientData(PropertyValuation $v): self
    {
        return new self(
            basePrice:     0,
            adjustedPrice: 0,
            totalLow:      0,
            totalMid:      0,
            totalHigh:     0,
            suggested:     0,
            diagnosis:     'insufficient_data',
            confidence:    'low',
            adjustments:   [],
        );
    }

    public function getDiagnosisLabel(): string
    {
        return match($this->diagnosis) {
            'on_market'        => 'En línea con el mercado',
            'above_market'     => 'Arriba del mercado',
            'opportunity'      => 'Oportunidad (debajo del mercado)',
            'insufficient_data'=> 'Datos insuficientes',
            default            => '—',
        };
    }

    public function getDiagnosisColor(): string
    {
        return match($this->diagnosis) {
            'on_market'    => 'green',
            'above_market' => 'red',
            'opportunity'  => 'blue',
            default        => 'yellow',
        };
    }

    public function isInsufficient(): bool
    {
        return $this->diagnosis === 'insufficient_data';
    }

    /** Variación total entre base y precio ajustado, en porcentaje */
    public function totalAdjustmentPercent(): float
    {
        if ($this->basePrice === 0.0) return 0;
        return round((($this->adjustedPrice - $this->basePrice) / $this->basePrice) * 100, 1);
    }
}
