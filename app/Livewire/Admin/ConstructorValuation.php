<?php

namespace App\Livewire\Admin;

use App\Models\MarketColonia;
use App\Services\ConstructorValuationService;
use Livewire\Component;

class ConstructorValuation extends Component
{
    // ─── Datos del terreno ────────────────────────────────────────────────────
    public ?int   $coloniaId = null;
    public string $m2Terreno = '';
    public string $frente    = '';
    public string $fondo     = '';

    // ─── Zonificación ─────────────────────────────────────────────────────────
    public string $zonificacionLabel = 'HM 6/30';   // texto libre — clave PDDU
    public string $cos               = '0.60';
    public string $cus               = '3.60';
    public string $pisos             = '6';

    // ─── Precio del terreno (dos modos independientes, sin sync automático) ───
    public string $precioTerreno   = '';   // total MXN  (modo 'total')
    public string $precioTerrenoM2 = '';   // $/m²       (modo 'per_m2')
    public string $precioMode      = 'total';

    // ─── Parámetros de construcción ───────────────────────────────────────────
    public string $costoConstruccion = '22000';
    public string $eficiencia        = '80';
    public string $tamanoDepto       = '65';
    public string $precioVentaM2     = '';

    // ─── Resultado ────────────────────────────────────────────────────────────
    public ?array $result = null;

    // ─── Aplica un preset de zonificación ────────────────────────────────────
    public function applyPreset(string $key): void
    {
        $presets = app(ConstructorValuationService::class)->getZonificaciones();
        $p = $presets[$key] ?? null;
        if ($p && $p['cos'] !== null) {
            $this->zonificacionLabel = match($key) {
                'H3_30'  => 'H 3/30',
                'H4_30'  => 'H 4/30',
                'HM4_30' => 'HM 4/30',
                'HM5_30' => 'HM 5/30',
                'HM6_30' => 'HM 6/30',
                'HM8_30' => 'HM 8/30',
                'HC4_30' => 'HC 4/30',
                'CB5_30' => 'CB 5/30',
                'N10'    => 'H Norma 10',
                default  => $this->zonificacionLabel,
            };
            $this->cos   = (string) $p['cos'];
            $this->cus   = (string) $p['cus'];
            $this->pisos = (string) $p['pisos'];
        }
        $this->recalculate();
    }

    // ─── Hooks de recálculo — solo para campos que tienen wire:model.live ─────
    //     Los campos de precio usan wire:model.blur y llaman recalculate()
    //     directamente a través del evento blur en el blade.

    public function updatedColoniaId(): void         { $this->recalculate(); }
    public function updatedCos(): void               { $this->recalculate(); }
    public function updatedCus(): void               { $this->recalculate(); }
    public function updatedPisos(): void             { $this->recalculate(); }
    public function updatedEficiencia(): void        { $this->recalculate(); }
    public function updatedTamanoDepto(): void       { $this->recalculate(); }
    public function updatedPrecioVentaM2(): void     { $this->recalculate(); }

    // ─── Cálculo (público para poder llamarse desde wire:change en la vista) ──
    public function recalculate(): void
    {
        $m2    = (float) $this->m2Terreno;
        $cos   = (float) $this->cos;
        $cus   = (float) $this->cus;
        $pisos = max(1, (int) $this->pisos);
        $cc    = max(1_000, (float) $this->costoConstruccion);
        $ef    = max(0.1, min(1.0, (float) $this->eficiencia / 100));
        $td    = max(1, (float) $this->tamanoDepto);
        $pvm2  = (float) str_replace([',', ' '], '', $this->precioVentaM2);

        // Precio del terreno: tomar del modo activo
        if ($this->precioMode === 'per_m2') {
            $perM2  = (float) str_replace([',', ' '], '', $this->precioTerrenoM2);
            $precio = $perM2 * $m2;
        } else {
            $precio = (float) str_replace([',', ' '], '', $this->precioTerreno);
        }

        if ($m2 < 10 || $cos <= 0 || $cus <= 0 || $precio <= 0) {
            $this->result = null;
            return;
        }

        $this->result = app(ConstructorValuationService::class)->calculate(
            m2Terreno:         $m2,
            cos:               $cos,
            cus:               $cus,
            pisos:             $pisos,
            precioTerreno:     $precio,
            costoConstruccion: $cc,
            eficiencia:        $ef,
            tamanoDepto:       $td,
            precioVentaM2:     $pvm2,
            coloniaId:         $this->coloniaId ? (int) $this->coloniaId : null,
        );
    }

    // ─── Render ───────────────────────────────────────────────────────────────
    public function render()
    {
        $colonias  = MarketColonia::with('zone')->published()->orderBy('name')->get()->groupBy('zone.name');
        $presets   = app(ConstructorValuationService::class)->getZonificaciones();

        return view('livewire.admin.constructor-valuation', compact('colonias', 'presets'));
    }
}
