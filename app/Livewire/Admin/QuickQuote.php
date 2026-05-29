<?php

namespace App\Livewire\Admin;

use App\Models\MarketColonia;
use App\Models\MarketZone;
use App\Services\QuickQuoteService;
use Livewire\Component;

class QuickQuote extends Component
{
    // ─── Inputs ───────────────────────────────────────────────────────
    public ?int    $coloniaId       = null;
    public string  $propertyType    = 'apartment';
    public ?float  $m2Construction  = null;
    public ?float  $m2Land          = null;
    public string  $ageCategory     = 'mid';

    // ─── Estado ───────────────────────────────────────────────────────
    public ?array  $result          = null;
    public bool    $calculating     = false;

    // ─── Modo widget (pre-llenado desde captación) ────────────────────
    public bool    $widgetMode      = false;   // true = solo muestra, false = formulario completo

    public function mount(
        ?int    $coloniaId      = null,
        string  $propertyType   = 'apartment',
        ?float  $m2Construction = null,
        ?float  $m2Land         = null,
        ?int    $yearBuilt      = null,
        bool    $widgetMode     = false,
    ): void {
        $this->coloniaId      = $coloniaId;
        $this->propertyType   = $propertyType;
        $this->m2Construction = $m2Construction;
        $this->m2Land         = $m2Land;
        $this->widgetMode     = $widgetMode;

        if ($yearBuilt) {
            $age = now()->year - $yearBuilt;
            $this->ageCategory = match(true) {
                $age <= 5  => 'new',
                $age <= 20 => 'mid',
                default    => 'old',
            };
        }

        // Si viene pre-llenado, calcular automáticamente
        if ($widgetMode && $coloniaId && $m2Construction) {
            $this->calculate();
        }
    }

    public function calculate(): void
    {
        $this->validate([
            'coloniaId'      => 'required|integer',
            'm2Construction' => 'required|numeric|min:10',
            'propertyType'   => 'required|in:apartment,house,office,land',
            'ageCategory'    => 'required|in:new,mid,old',
        ]);

        $this->result = app(QuickQuoteService::class)->calculate(
            coloniaId:      $this->coloniaId,
            propertyType:   $this->propertyType,
            m2Construction: (float) $this->m2Construction,
            m2Land:         (float) ($this->m2Land ?? 0),
            ageCategory:    $this->ageCategory,
        );
    }

    public function render()
    {
        $colonias = MarketColonia::with('zone')
            ->published()
            ->orderBy('name')
            ->get()
            ->groupBy('zone.name');

        return view('livewire.admin.quick-quote', compact('colonias'));
    }
}
