<?php

namespace App\Livewire\Admin;

use App\Models\Captacion;
use App\Services\PresentationGeneratorService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * Editor reactivo de la presentación inicial.
 * Cambios en precio, comisión o plan de marketing regeneran el PDF en < 2s.
 * Livewire aprobado en CRM admin por Alex (2026-04-29).
 */
class PresentationEditor extends Component
{
    public int    $captacionId;
    public string $commission_pct  = '5';   // para venta = %; para renta = meses
    public string $price_suggested = '';
    public string $marketing_plan  = '';
    public string $intent          = 'general';  // para saber si es renta o venta

    // Token del send más reciente (para "Ver como propietario")
    public ?string $latestToken = null;

    // URL del PDF actual para el iframe
    public string $pdfUrl = '';

    // Estado de envío
    public string $emailStatus = '';
    public string $waUrl       = '';

    public function mount(Captacion $captacion): void
    {
        $this->captacionId     = $captacion->id;
        $this->intent          = $captacion->intent ?? 'general';
        $this->commission_pct  = (string)($captacion->commission_pct ?? ($this->isRenta() ? 1 : 5));
        $this->marketing_plan  = $captacion->marketing_plan ?? '';
        $this->price_suggested = $captacion->property?->price > 0
            ? '$' . number_format($captacion->property->price, 0) . ' MXN'
            : '';

        $this->latestToken = $captacion->sends()->latest()->first()?->tracking_token;
        $this->pdfUrl = route('admin.captaciones.presentation.pdf', $captacion->id);
    }

    // ─── Regeneración reactiva (debounce 600ms en la vista) ───────────────────

    public function updatedCommissionPct(): void  { $this->regenerate(); }
    public function updatedPriceSuggested(): void { $this->regenerate(); }
    public function updatedMarketingPlan(): void  { $this->regenerate(); }

    public function regenerate(): void
    {
        $captacion = Captacion::find($this->captacionId);
        if (!$captacion) return;

        // Persistir cambios en la captación
        $captacion->update([
            'commission_pct' => (float)$this->commission_pct ?: 5,
            'marketing_plan' => $this->marketing_plan,
        ]);

        // Regenerar PDF con los overrides
        $svc = app(PresentationGeneratorService::class);
        $svc->generatePdf($captacion, [
            'commission_pct'  => (float)$this->commission_pct ?: 5,
            'price_suggested' => $this->price_suggested ?: null,
            'marketing_plan'  => $this->marketing_plan,
        ]);

        // Forzar recarga del iframe con cache-buster
        $this->pdfUrl = route('admin.captaciones.presentation.pdf', $captacion->id) . '?v=' . time();
        $this->dispatch('pdfUrlUpdated', url: $this->pdfUrl);
    }

    public function isRenta(): bool
    {
        return str_starts_with($this->intent, 'renta_');
    }

    public function render()
    {
        $captacion = Captacion::with(['client', 'property', 'sends'])->find($this->captacionId);

        // Obtener datos de mercado si existen
        $marketSnapshot = app(\App\Services\PresentationGeneratorService::class)
            ->getMarketSnapshot($captacion);

        return view('livewire.admin.presentation-editor', compact('captacion', 'marketSnapshot'));
    }
}
