<?php

namespace App\Livewire\Admin;

use App\Models\MarketColonia;
use App\Services\ConstructorValuationService;
use Livewire\Component;

class ConstructorValuation extends Component
{
    // ─── Datos del terreno ────────────────────────────────────────────────────
    public ?int   $coloniaId   = null;
    public string $m2Terreno   = '';
    public string $frente      = '';   // metros de frente (informativo)
    public string $fondo       = '';   // metros de fondo (informativo)

    // ─── Zonificación urbana ──────────────────────────────────────────────────
    public string $zonificacion = 'HM6_30';   // preset seleccionado
    public string $cos          = '0.60';
    public string $cus          = '3.60';
    public string $pisos        = '6';

    // ─── Precio del terreno ───────────────────────────────────────────────────
    public string $precioTerreno   = '';       // total en MXN
    public string $precioTerrenoM2 = '';       // $/m² (alternativo)
    public string $precioMode      = 'total';  // 'total' | 'per_m2'

    // ─── Parámetros de construcción ───────────────────────────────────────────
    public string $costoConstruccion = '22000';   // MXN/m² bruto
    public string $eficiencia        = '80';       // % vendible
    public string $tamanoDepto       = '65';       // m² promedio/depto
    public string $precioVentaM2     = '';         // 0 = usar mercado

    // ─── Resultado ────────────────────────────────────────────────────────────
    public ?array $result = null;

    // ─── Presets de zonificación → auto-fill COS/CUS/pisos ───────────────────

    public function updatedZonificacion(string $value): void
    {
        $zonificaciones = app(ConstructorValuationService::class)->getZonificaciones();
        $preset = $zonificaciones[$value] ?? null;

        if ($preset && $preset['cos'] !== null) {
            $this->cos   = (string) $preset['cos'];
            $this->cus   = (string) $preset['cus'];
            $this->pisos = (string) $preset['pisos'];
        }

        $this->recalculate();
    }

    // ─── Precio: sincronizar total ↔ por m² ──────────────────────────────────

    public function updatedPrecioTerreno(): void
    {
        if ($this->precioMode === 'total' && $this->m2Terreno > 0) {
            $total = (float) str_replace([',', ' '], '', $this->precioTerreno);
            $m2    = (float) $this->m2Terreno;
            if ($total > 0 && $m2 > 0) {
                $this->precioTerrenoM2 = (string) (int) round($total / $m2);
            }
        }
        $this->recalculate();
    }

    public function updatedPrecioTerrenoM2(): void
    {
        if ($this->precioMode === 'per_m2' && $this->m2Terreno > 0) {
            $perM2 = (float) str_replace([',', ' '], '', $this->precioTerrenoM2);
            $m2    = (float) $this->m2Terreno;
            if ($perM2 > 0 && $m2 > 0) {
                $this->precioTerreno = (string) (int) round($perM2 * $m2);
            }
        }
        $this->recalculate();
    }

    public function updatedM2Terreno(): void
    {
        // Actualizar precio total si estamos en modo por m²
        if ($this->precioMode === 'per_m2' && $this->precioTerrenoM2) {
            $perM2 = (float) str_replace([',', ' '], '', $this->precioTerrenoM2);
            $m2    = (float) $this->m2Terreno;
            if ($perM2 > 0 && $m2 > 0) {
                $this->precioTerreno = (string) (int) round($perM2 * $m2);
            }
        }
        $this->recalculate();
    }

    // ─── Hooks de recálculo ───────────────────────────────────────────────────

    public function updatedColoniaId(): void          { $this->recalculate(); }
    public function updatedCos(): void                { $this->recalculate(); }
    public function updatedCus(): void                { $this->recalculate(); }
    public function updatedPisos(): void              { $this->recalculate(); }
    public function updatedCostoConstruccion(): void  { $this->recalculate(); }
    public function updatedEficiencia(): void         { $this->recalculate(); }
    public function updatedTamanoDepto(): void        { $this->recalculate(); }
    public function updatedPrecioVentaM2(): void      { $this->recalculate(); }

    // ─── Cálculo ─────────────────────────────────────────────────────────────

    private function recalculate(): void
    {
        $m2     = (float) $this->m2Terreno;
        $cos    = (float) $this->cos;
        $cus    = (float) $this->cus;
        $pisos  = max(1, (int) $this->pisos);
        $precio = (float) str_replace([',', ' '], '', $this->precioTerreno);
        $cc     = max(1, (float) $this->costoConstruccion);
        $ef     = max(0.1, min(1.0, (float) $this->eficiencia / 100));
        $td     = max(1, (float) $this->tamanoDepto);
        $pvm2   = (float) str_replace([',', ' '], '', $this->precioVentaM2);

        if ($m2 < 10 || $cos <= 0 || $cus <= 0 || $precio <= 0) {
            $this->result = null;
            return;
        }

        $svc = app(ConstructorValuationService::class);

        $this->result = $svc->calculate(
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
        $colonias       = MarketColonia::with('zone')->published()->orderBy('name')->get()->groupBy('zone.name');
        $zonificaciones = app(ConstructorValuationService::class)->getZonificaciones();

        return view('livewire.admin.constructor-valuation', compact('colonias', 'zonificaciones'));
    }
}
