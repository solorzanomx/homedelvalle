{{-- wire:poll activo solo cuando hay jobs en proceso --}}
<div @if($hasActiveJobs) wire:poll.3000ms @endif>

{{-- ═══════════════════════════════════════════════════════════════
     BANNER DE PROGRESO (visible solo cuando hay runs activos)
════════════════════════════════════════════════════════════════ --}}
@if($totalRuns > 0)
<div class="progress-banner {{ $hasActiveJobs ? 'active' : ($failedRuns > 0 ? 'has-errors' : 'all-done') }}"
     style="margin-bottom:1rem;">
    <div class="progress-banner-left">
        @if($hasActiveJobs)
            <div class="progress-spinner"></div>
            <div>
                <div class="progress-title">Actualizando precios del observatorio...</div>
                <div class="progress-sub">
                    {{ $doneRuns }} completadas · {{ $pendingRuns }} en proceso · {{ $failedRuns > 0 ? $failedRuns . ' con error' : '' }}
                    <span style="color:var(--text-muted);"> — Puedes navegar a otras secciones, esto continúa en segundo plano.</span>
                </div>
            </div>
        @elseif($failedRuns > 0)
            <span style="font-size:1.1rem;">⚠️</span>
            <div>
                <div class="progress-title" style="color:#92400e;">Actualización completada con errores</div>
                <div class="progress-sub">{{ $doneRuns }} OK · {{ $failedRuns }} fallaron (revisa los detalles en cada card)</div>
            </div>
        @else
            <span style="font-size:1.1rem;">✅</span>
            <div>
                <div class="progress-title" style="color:#065f46;">¡Actualización completada!</div>
                <div class="progress-sub">{{ $doneRuns }} colonias actualizadas correctamente.</div>
            </div>
        @endif
    </div>

    @if($totalRuns > 0)
    <div class="progress-bar-wrap">
        @php
            $pct = $totalRuns > 0 ? round((($doneRuns + $failedRuns) / $totalRuns) * 100) : 0;
        @endphp
        <div class="progress-bar-track">
            <div class="progress-bar-fill {{ $failedRuns > 0 && !$hasActiveJobs ? 'has-errors' : '' }}"
                 style="width:{{ $pct }}%"></div>
        </div>
        <div class="progress-pct">{{ $pct }}%</div>
    </div>
    @endif
</div>
@endif

{{-- ═══════════════════════════════════════════════════════════════
     PAGE HEADER
════════════════════════════════════════════════════════════════ --}}
<div class="page-header" style="align-items:flex-start; flex-wrap:wrap; gap:0.75rem;">
    <div>
        <h2>Precios de Mercado · Benito Juárez</h2>
        <p class="text-muted" style="margin:0;">
            @if($lastPeriod)
                Última actualización: <strong>{{ \Carbon\Carbon::parse($lastPeriod)->translatedFormat('F Y') }}</strong> ·
            @else
                <strong>Sin datos aún.</strong> ·
            @endif
            <strong style="color:var(--text);">{{ $activeColonias }}</strong> de
            <strong style="color:var(--text);">{{ $totalColonias }}</strong> colonias activas en el sitio
        </p>
    </div>
    <div style="display:flex; gap:0.5rem; flex-wrap:wrap; margin-left:auto;">
        {{-- Botón Venta --}}
        <button wire:click="runUpdate('all', 'sale')"
                wire:loading.attr="disabled"
                wire:loading.class="btn-loading"
                wire:target="runUpdate"
                class="btn btn-outline"
                style="display:inline-flex;align-items:center;gap:.4rem;">
            <span wire:loading.remove wire:target="runUpdate">↺ Actualizar precios de venta</span>
            <span wire:loading wire:target="runUpdate" style="display:none;">
                <span class="btn-spinner"></span> Despachando...
            </span>
        </button>
        {{-- Botón Renta --}}
        <button wire:click="runUpdate('all', 'rent')"
                wire:loading.attr="disabled"
                wire:loading.class="btn-loading"
                wire:target="runUpdate"
                class="btn btn-primary"
                style="display:inline-flex;align-items:center;gap:.4rem;background:#7c3aed;border-color:#7c3aed;">
            <span wire:loading.remove wire:target="runUpdate">↺ Actualizar precios de renta</span>
            <span wire:loading wire:target="runUpdate" style="display:none;">
                <span class="btn-spinner"></span> Despachando...
            </span>
        </button>
    </div>
</div>

{{-- Flash messages --}}
@if(session('error'))
<div class="alert alert-danger" style="margin-bottom:1rem;">
    {{ session('error') }}
    <button class="alert-close" onclick="this.parentElement.remove()">×</button>
</div>
@endif

<div style="background:#fffbeb;border:1px solid #fde68a;border-radius:var(--radius);padding:0.65rem 1rem;font-size:0.8rem;color:#92400e;margin-bottom:1.5rem;">
    ⚠️ Cada actualización llama a <strong>Perplexity + Claude</strong> por colonia × tipo ≈ 4 llamadas/colonia.
    Costo estimado: <strong>~$0.10–0.30 USD</strong> por colonia. Activa solo las colonias donde operas.
</div>

{{-- ═══════════════════════════════════════════════════════════════
     ZONAS + CARDS
════════════════════════════════════════════════════════════════ --}}
@foreach($zones as $zone)
@php
    $total   = $zone->colonias->count();
    $activas = $zone->colonias->where('is_published', true)->count();
@endphp
<div class="zone-section">
    <div class="zone-header">
        <span class="zone-title">{{ $zone->name }}</span>
        <span class="zone-pill {{ $activas > 0 ? 'active' : '' }}">
            {{ $activas }}/{{ $total }} activas
        </span>
    </div>

    <div class="colonia-grid">
        @foreach($zone->colonias as $colonia)
        @php
            $runSale = $recentRuns->get($colonia->id . '_sale');
            $runRent = $recentRuns->get($colonia->id . '_rent');

            $allSnaps    = $colonia->snapshots->sortByDesc('period');
            $saleSnaps   = $allSnaps->where('operation_type', 'sale')->groupBy('property_type');
            $rentSnaps   = $allSnaps->where('operation_type', 'rent')->groupBy('property_type');
            $legacySnaps = $allSnaps->whereNotIn('operation_type', ['sale','rent'])->groupBy('property_type');
            foreach ($legacySnaps as $pt => $items) {
                if (!isset($saleSnaps[$pt])) $saleSnaps[$pt] = $items;
            }
            $latestApt   = $saleSnaps['apartment'] ?? collect();
            $latestHouse = $saleSnaps['house']     ?? collect();
            $rentApt     = $rentSnaps['apartment'] ?? collect();
            $rentOffice  = $rentSnaps['office']    ?? collect();
            $period      = $latestApt->first()?->period ?? $latestHouse->first()?->period;
            $confidence  = $latestApt->first()?->confidence ?? $latestHouse->first()?->confidence;
            $confColor   = match($confidence) { 'high' => '#16a34a', 'medium' => '#d97706', default => '#94a3b8' };
            $isActive    = $colonia->is_published;
            $hasRentData = $rentApt->isNotEmpty() || $rentOffice->isNotEmpty();
        @endphp
        <div class="colonia-card {{ $isActive ? '' : 'inactive' }}"
             wire:key="colonia-{{ $colonia->id }}">

            {{-- Header: nombre + toggle --}}
            <div class="card-top">
                <div>
                    <div class="colonia-name">{{ $colonia->name }}</div>
                    @if($colonia->cp)
                    <div class="colonia-cp">CP {{ $colonia->cp }}</div>
                    @endif
                </div>

                {{-- Toggle switch via Livewire --}}
                <div class="toggle-form" title="{{ $isActive ? 'Desactivar del sitio' : 'Activar en el sitio' }}">
                    <span class="toggle-label">{{ $isActive ? 'Activa' : 'Oculta' }}</span>
                    <label class="toggle-wrap">
                        <input type="checkbox" {{ $isActive ? 'checked' : '' }}
                               wire:click="toggleColonia({{ $colonia->id }})"
                               wire:loading.attr="disabled"
                               wire:target="toggleColonia({{ $colonia->id }})">
                        <span class="toggle-slider"></span>
                    </label>
                </div>
            </div>

            {{-- Status badges de jobs --}}
            @if($runSale || $runRent)
            <div class="run-badges">
                @if($runSale)
                <div class="run-badge {{ $runSale->status }}">
                    @if($runSale->isActive())
                        <span class="run-spinner"></span>
                    @elseif($runSale->isDone())
                        <span>✓</span>
                    @else
                        <span>✗</span>
                    @endif
                    Venta · {{ $runSale->status_label }}
                    @if($runSale->completed_at)
                        <span class="run-time">{{ $runSale->completed_at->diffForHumans(short: true) }}</span>
                    @endif
                </div>
                @endif
                @if($runRent)
                <div class="run-badge {{ $runRent->status }}" style="border-color:#c4b5fd;background:#faf5ff;">
                    @if($runRent->isActive())
                        <span class="run-spinner" style="border-top-color:#7c3aed;"></span>
                    @elseif($runRent->isDone())
                        <span style="color:#7c3aed;">✓</span>
                    @else
                        <span>✗</span>
                    @endif
                    <span style="color:#6d28d9;">Renta · {{ $runRent->status_label }}</span>
                    @if($runRent->completed_at)
                        <span class="run-time">{{ $runRent->completed_at->diffForHumans(short: true) }}</span>
                    @endif
                </div>
                @endif
            </div>
            @endif

            {{-- Period tag --}}
            @if($period)
            <span class="period-tag">
                @if($confidence)
                <span class="conf-dot" style="background:{{ $confColor }}"></span>
                @endif
                {{ \Carbon\Carbon::parse($period)->translatedFormat('M Y') }}
            </span>
            @endif

            {{-- VENTA --}}
            <div class="price-section">
                @php $ageMap = ['new'=>'Nuevo','mid'=>'Seminuevo','old'=>'Antiguo']; @endphp
                @if($latestApt->isNotEmpty())
                <div class="price-type-label" style="margin-top:.25rem;">DEPARTAMENTOS · MXN/m²</div>
                @foreach($latestApt->take(3) as $snap)
                <div class="price-row">
                    <span class="price-cat">{{ $ageMap[$snap->age_category] ?? $snap->age_category }}</span>
                    <span class="price-val">${{ number_format($snap->price_m2_avg) }}</span>
                </div>
                @endforeach
                @endif
                @if($latestHouse->isNotEmpty())
                <div class="price-type-label" style="margin-top:.5rem;">CASAS · MXN/m²</div>
                @foreach($latestHouse->take(3) as $snap)
                <div class="price-row">
                    <span class="price-cat">{{ $ageMap[$snap->age_category] ?? $snap->age_category }}</span>
                    <span class="price-val">${{ number_format($snap->price_m2_avg) }}</span>
                </div>
                @endforeach
                @endif
                @if($latestApt->isEmpty() && $latestHouse->isEmpty())
                <div class="no-data">Sin datos de venta</div>
                @endif
            </div>

            {{-- RENTA --}}
            @if($hasRentData)
            <div class="price-section" style="margin-top:.5rem;padding-top:.5rem;border-top:1px dashed #c4b5fd;">
                @if($rentApt->isNotEmpty())
                <div class="price-type-label" style="color:#7c3aed;">DEPTO RENTA · $/m²/mes</div>
                @foreach($rentApt->take(3) as $snap)
                <div class="price-row">
                    <span class="price-cat">{{ $ageMap[$snap->age_category] ?? $snap->age_category }}</span>
                    <span class="price-val" style="color:#7c3aed;">${{ number_format($snap->price_m2_avg) }}</span>
                </div>
                @endforeach
                @endif
                @if($rentOffice->isNotEmpty())
                <div class="price-type-label" style="color:#7c3aed;margin-top:.35rem;">LOCAL/OFICINA RENTA · $/m²/mes</div>
                @foreach($rentOffice->take(3) as $snap)
                <div class="price-row">
                    <span class="price-cat">{{ $ageMap[$snap->age_category] ?? $snap->age_category }}</span>
                    <span class="price-val" style="color:#7c3aed;">${{ number_format($snap->price_m2_avg) }}</span>
                </div>
                @endforeach
                @endif
            </div>
            @else
            <div style="font-size:.68rem;color:#a78bfa;font-style:italic;margin-top:.4rem;padding-top:.4rem;border-top:1px dashed #ddd8fe;">
                Sin datos de renta aún
            </div>
            @endif

            {{-- Acciones: Venta + Renta por colonia --}}
            <div class="card-actions" style="gap:.3rem;">
                <button wire:click="runUpdate('{{ $colonia->id }}', 'sale')"
                        wire:loading.attr="disabled"
                        wire:target="runUpdate('{{ $colonia->id }}', 'sale')"
                        class="btn btn-outline btn-sm btn-xs"
                        style="flex:1;position:relative;"
                        {{ $runSale?->isActive() ? 'disabled' : '' }}>
                    <span wire:loading.remove wire:target="runUpdate('{{ $colonia->id }}', 'sale')">
                        @if($runSale?->isActive()) <span class="run-spinner-sm"></span> @else ↺ @endif Venta
                    </span>
                    <span wire:loading wire:target="runUpdate('{{ $colonia->id }}', 'sale')">
                        <span class="run-spinner-sm"></span> ...
                    </span>
                </button>
                <button wire:click="runUpdate('{{ $colonia->id }}', 'rent')"
                        wire:loading.attr="disabled"
                        wire:target="runUpdate('{{ $colonia->id }}', 'rent')"
                        class="btn btn-sm btn-xs"
                        style="flex:1;background:#7c3aed;color:#fff;border:none;position:relative;"
                        {{ $runRent?->isActive() ? 'disabled' : '' }}>
                    <span wire:loading.remove wire:target="runUpdate('{{ $colonia->id }}', 'rent')">
                        @if($runRent?->isActive()) <span class="run-spinner-sm" style="border-top-color:#fff;"></span> @else ↺ @endif Renta
                    </span>
                    <span wire:loading wire:target="runUpdate('{{ $colonia->id }}', 'rent')">
                        <span class="run-spinner-sm" style="border-top-color:#fff;"></span> ...
                    </span>
                </button>
            </div>

            {{-- Error detail --}}
            @if($runSale?->isFailed() && $runSale->error_msg)
            <div class="run-error-msg">⚠ Venta: {{ Str::limit($runSale->error_msg, 80) }}</div>
            @endif
            @if($runRent?->isFailed() && $runRent->error_msg)
            <div class="run-error-msg">⚠ Renta: {{ Str::limit($runRent->error_msg, 80) }}</div>
            @endif

        </div>
        @endforeach
    </div>
</div>
@endforeach

</div>{{-- /wire:poll wrapper --}}
