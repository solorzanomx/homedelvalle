{{-- Polling activo solo cuando hay jobs en proceso --}}
<div @if($hasActiveJobs) wire:poll.4000ms @endif>

{{-- ═══ BANNER DE PROGRESO ═══════════════════════════════════════════════════ --}}
@if($totalRuns > 0)
<div class="progress-banner {{ $hasActiveJobs ? 'active' : ($failedRuns > 0 ? 'has-errors' : 'all-done') }}"
     style="margin-bottom:1rem;">
    <div class="progress-banner-left">
        @if($hasActiveJobs)
            <div class="progress-spinner"></div>
            <div>
                <div class="progress-title">Actualizando precios del observatorio...</div>
                <div class="progress-sub">
                    {{ $doneRuns }} zonas completadas · {{ $pendingRuns }} en proceso
                    @if($failedRuns > 0) · {{ $failedRuns }} con error @endif
                    <span style="color:var(--text-muted);"> — Puedes navegar a otras secciones, esto continúa en segundo plano.</span>
                </div>
            </div>
        @elseif($failedRuns > 0)
            <span style="font-size:1.1rem;">⚠️</span>
            <div>
                <div class="progress-title" style="color:#92400e;">Actualización completada con errores</div>
                <div class="progress-sub">{{ $doneRuns }} OK · {{ $failedRuns }} fallaron</div>
            </div>
        @else
            <span style="font-size:1.1rem;">✅</span>
            <div>
                <div class="progress-title" style="color:#065f46;">¡Actualización completada!</div>
                <div class="progress-sub">{{ $doneRuns }} zonas actualizadas correctamente.</div>
            </div>
        @endif
    </div>
    @if($totalRuns > 0)
    <div class="progress-bar-wrap">
        @php $pct = $totalRuns > 0 ? round((($doneRuns + $failedRuns) / $totalRuns) * 100) : 0; @endphp
        <div class="progress-bar-track">
            <div class="progress-bar-fill {{ $failedRuns > 0 && !$hasActiveJobs ? 'has-errors' : '' }}"
                 style="width:{{ $pct }}%"></div>
        </div>
        <div class="progress-pct">{{ $pct }}%</div>
    </div>
    @endif
</div>
@endif

{{-- ═══ PAGE HEADER ═══════════════════════════════════════════════════════════ --}}
<div class="page-header" style="align-items:flex-start;flex-wrap:wrap;gap:0.75rem;">
    <div>
        <h2>Observatorio de Precios · Benito Juárez</h2>
        <p class="text-muted" style="margin:0;">
            @if($lastPeriod)
                Última actualización: <strong>{{ \Carbon\Carbon::parse($lastPeriod)->translatedFormat('F Y') }}</strong> ·
            @else
                <strong>Sin datos aún.</strong> ·
            @endif
            <strong style="color:var(--text);">{{ $zones->count() }}</strong> zonas ·
            <strong style="color:var(--text);">{{ $zones->sum(fn($z) => $z->colonias->count()) }}</strong> colonias
        </p>
    </div>
    <div style="display:flex;gap:0.5rem;flex-wrap:wrap;margin-left:auto;">
        <button wire:click="runAll('sale')"
                wire:loading.attr="disabled" wire:loading.class="btn-loading"
                wire:target="runAll"
                class="btn btn-outline"
                style="display:inline-flex;align-items:center;gap:.4rem;">
            <span wire:loading.remove wire:target="runAll">↺ Actualizar precios de venta</span>
            <span wire:loading wire:target="runAll" style="display:none;"><span class="btn-spinner"></span> Despachando...</span>
        </button>
        <button wire:click="runAll('rent')"
                wire:loading.attr="disabled" wire:loading.class="btn-loading"
                wire:target="runAll"
                class="btn btn-primary"
                style="display:inline-flex;align-items:center;gap:.4rem;background:#7c3aed;border-color:#7c3aed;">
            <span wire:loading.remove wire:target="runAll">↺ Actualizar precios de renta</span>
            <span wire:loading wire:target="runAll" style="display:none;"><span class="btn-spinner"></span> Despachando...</span>
        </button>
    </div>
</div>

<div style="background:#fffbeb;border:1px solid #fde68a;border-radius:var(--radius);padding:0.65rem 1rem;font-size:0.8rem;color:#92400e;margin-bottom:1.5rem;">
    ⚠️ Cada zona usa <strong>Perplexity sonar-pro + Claude Haiku</strong> → ~15–20 listings por zona con clasificación por antigüedad.
    Costo estimado: <strong>~$0.30–0.60 USD</strong> por zona.
</div>

{{-- ═══ GRID DE ZONAS ════════════════════════════════════════════════════════ --}}
<div class="zone-cards-grid">

@foreach($zones as $zone)
@php
    $snaps      = $allSnapshots->get($zone->id, collect());
    $runSale    = $recentRuns->get($zone->id . '_sale');
    $runRent    = $recentRuns->get($zone->id . '_rent');

    // Agrupar snapshots: ['sale' => ['apartment' => ['new'=>snap, 'mid'=>snap, 'old'=>snap], ...], 'rent' => ...]
    $grouped = [];
    foreach ($snaps->sortByDesc('period') as $snap) {
        $op   = $snap->operation_type;
        $prop = $snap->property_type;
        $age  = $snap->age_category;
        if (!isset($grouped[$op][$prop][$age])) {
            $grouped[$op][$prop][$age] = $snap;
        }
    }

    $saleApt   = $grouped['sale']['apartment'] ?? [];
    $saleHouse = $grouped['sale']['house']     ?? [];
    $rentApt   = $grouped['rent']['apartment'] ?? [];
    $rentOffice= $grouped['rent']['office']    ?? [];

    $hasSaleData = !empty($saleApt) || !empty($saleHouse);
    $hasRentData = !empty($rentApt) || !empty($rentOffice);

    $lastSalePeriod = collect($saleApt)->first()?->period ?? collect($saleHouse)->first()?->period;
    $lastRentPeriod = collect($rentApt)->first()?->period;

    $ageMap  = ['new' => 'Nuevo', 'mid' => 'Seminuevo', 'old' => 'Antiguo'];
    $confColor = fn($c) => match($c) { 'high' => '#16a34a', 'medium' => '#d97706', default => '#94a3b8' };
@endphp

<div class="zone-card" wire:key="zone-{{ $zone->id }}">

    {{-- Cabecera zona --}}
    <div class="zone-card-header">
        <div style="flex:1;min-width:0;">
            <div class="zone-card-title">{{ $zone->name }}</div>
            <div class="zone-card-colonias">{{ $zone->colonias->pluck('name')->implode(' · ') }}</div>
        </div>
        <div style="display:flex;flex-direction:column;align-items:flex-end;gap:4px;flex-shrink:0;">
            {{-- Status badges --}}
            @if($runSale)
            <div class="run-badge {{ $runSale->status }}">
                @if($runSale->isActive())<span class="run-spinner"></span>@elseif($runSale->isDone())✓@else✗@endif
                Venta · {{ $runSale->status_label }}
            </div>
            @endif
            @if($runRent)
            <div class="run-badge {{ $runRent->status }}" style="border-color:#c4b5fd;background:#faf5ff;">
                @if($runRent->isActive())<span class="run-spinner" style="border-top-color:#7c3aed;"></span>@elseif($runRent->isDone())<span style="color:#7c3aed;">✓</span>@else✗@endif
                <span style="color:#6d28d9;">Renta · {{ $runRent->status_label }}</span>
            </div>
            @endif
        </div>
    </div>

    {{-- ── VENTA ──────────────────────────────────────────────────── --}}
    @if($hasSaleData)
    <div class="zone-prices-section">
        <div class="zone-section-header">
            <span class="zone-section-label">VENTA</span>
            @if($lastSalePeriod)
            <span class="zone-period-tag">
                <span class="conf-dot" style="background:{{ ($confColor)(collect($saleApt)->first()?->confidence) }}"></span>
                {{ \Carbon\Carbon::parse($lastSalePeriod)->translatedFormat('M Y') }}
                @php $totalListings = collect($saleApt)->sum('sample_size') + collect($saleHouse)->sum('sample_size'); @endphp
                @if($totalListings > 0) · {{ $totalListings }} listings @endif
            </span>
            @endif
        </div>

        {{-- Departamentos --}}
        @if(!empty($saleApt))
        <div class="prop-type-label">🏢 Departamentos · MXN/m²</div>
        <div class="price-age-rows">
            @foreach(['new','mid','old'] as $age)
            @if(isset($saleApt[$age]))
            @php $s = $saleApt[$age]; @endphp
            <div class="price-age-row">
                <span class="age-label {{ $s->confidence }}">{{ $ageMap[$age] }}</span>
                <span class="price-range">
                    ${{ number_format($s->price_m2_low) }}
                    <span class="price-avg">${{ number_format($s->price_m2_avg) }}</span>
                    ${{ number_format($s->price_m2_high) }}
                </span>
            </div>
            @endif
            @endforeach
        </div>
        @endif

        {{-- Casas --}}
        @if(!empty($saleHouse))
        <div class="prop-type-label" style="margin-top:.5rem;">🏠 Casas · MXN/m²</div>
        <div class="price-age-rows">
            @foreach(['new','mid','old'] as $age)
            @if(isset($saleHouse[$age]))
            @php $s = $saleHouse[$age]; @endphp
            <div class="price-age-row">
                <span class="age-label {{ $s->confidence }}">{{ $ageMap[$age] }}</span>
                <span class="price-range">
                    ${{ number_format($s->price_m2_low) }}
                    <span class="price-avg">${{ number_format($s->price_m2_avg) }}</span>
                    ${{ number_format($s->price_m2_high) }}
                </span>
            </div>
            @endif
            @endforeach
        </div>
        @endif
    </div>
    @elseif(!$runSale?->isActive())
    <div class="zone-no-data">Sin datos de venta</div>
    @endif

    {{-- ── RENTA ──────────────────────────────────────────────────── --}}
    @if($hasRentData)
    <div class="zone-prices-section zone-rent-section">
        <div class="zone-section-header">
            <span class="zone-section-label" style="color:#7c3aed;">RENTA</span>
            @if($lastRentPeriod)
            <span class="zone-period-tag">
                <span class="conf-dot" style="background:{{ ($confColor)(collect($rentApt)->first()?->confidence) }}"></span>
                {{ \Carbon\Carbon::parse($lastRentPeriod)->translatedFormat('M Y') }}
                @php $totalRentListings = collect($rentApt)->sum('sample_size') + collect($rentOffice)->sum('sample_size'); @endphp
                @if($totalRentListings > 0) · {{ $totalRentListings }} listings @endif
            </span>
            @endif
        </div>

        @if(!empty($rentApt))
        <div class="prop-type-label" style="color:#7c3aed;">🏢 Depto · $/m²/mes</div>
        <div class="price-age-rows">
            @foreach(['new','mid','old'] as $age)
            @if(isset($rentApt[$age]))
            @php $s = $rentApt[$age]; @endphp
            <div class="price-age-row">
                <span class="age-label {{ $s->confidence }}">{{ $ageMap[$age] }}</span>
                <span class="price-range" style="color:#7c3aed;">
                    ${{ number_format($s->price_m2_low) }}
                    <span class="price-avg">${{ number_format($s->price_m2_avg) }}</span>
                    ${{ number_format($s->price_m2_high) }}
                </span>
            </div>
            @endif
            @endforeach
        </div>
        @endif

        @if(!empty($rentOffice))
        <div class="prop-type-label" style="color:#7c3aed;margin-top:.5rem;">🏪 Local/Oficina · $/m²/mes</div>
        <div class="price-age-rows">
            @foreach(['new','mid','old'] as $age)
            @if(isset($rentOffice[$age]))
            @php $s = $rentOffice[$age]; @endphp
            <div class="price-age-row">
                <span class="age-label {{ $s->confidence }}">{{ $ageMap[$age] }}</span>
                <span class="price-range" style="color:#7c3aed;">
                    ${{ number_format($s->price_m2_low) }}
                    <span class="price-avg">${{ number_format($s->price_m2_avg) }}</span>
                    ${{ number_format($s->price_m2_high) }}
                </span>
            </div>
            @endif
            @endforeach
        </div>
        @endif
    </div>
    @elseif(!$runRent?->isActive())
    <div class="zone-no-data" style="border-color:#ddd8fe;color:#a78bfa;">Sin datos de renta</div>
    @endif

    {{-- ── Botones de actualización ───────────────────────────────── --}}
    <div class="zone-card-actions">
        <button wire:click="runUpdate({{ $zone->id }}, 'sale')"
                wire:loading.attr="disabled"
                wire:target="runUpdate({{ $zone->id }}, 'sale')"
                class="btn btn-outline btn-sm btn-xs"
                style="flex:1;"
                {{ $runSale?->isActive() ? 'disabled' : '' }}>
            <span wire:loading.remove wire:target="runUpdate({{ $zone->id }}, 'sale')">
                @if($runSale?->isActive())<span class="run-spinner-sm"></span>@else ↺@endif Venta
            </span>
            <span wire:loading wire:target="runUpdate({{ $zone->id }}, 'sale')">
                <span class="run-spinner-sm"></span> ...
            </span>
        </button>
        <button wire:click="runUpdate({{ $zone->id }}, 'rent')"
                wire:loading.attr="disabled"
                wire:target="runUpdate({{ $zone->id }}, 'rent')"
                class="btn btn-sm btn-xs"
                style="flex:1;background:#7c3aed;color:#fff;border:none;"
                {{ $runRent?->isActive() ? 'disabled' : '' }}>
            <span wire:loading.remove wire:target="runUpdate({{ $zone->id }}, 'rent')">
                @if($runRent?->isActive())<span class="run-spinner-sm" style="border-top-color:#fff;"></span>@else ↺@endif Renta
            </span>
            <span wire:loading wire:target="runUpdate({{ $zone->id }}, 'rent')">
                <span class="run-spinner-sm" style="border-top-color:#fff;"></span> ...
            </span>
        </button>
    </div>

    {{-- Error detail --}}
    @if($runSale?->isFailed() && $runSale->error_msg)
    <div class="run-error-msg">⚠ Venta: {{ Str::limit($runSale->error_msg, 90) }}</div>
    @endif
    @if($runRent?->isFailed() && $runRent->error_msg)
    <div class="run-error-msg">⚠ Renta: {{ Str::limit($runRent->error_msg, 90) }}</div>
    @endif

</div>
@endforeach

</div>{{-- /zone-cards-grid --}}
</div>{{-- /wire:poll wrapper --}}
