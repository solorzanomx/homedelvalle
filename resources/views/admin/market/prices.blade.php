@extends('layouts.app-sidebar')
@section('title', 'Precios de Mercado')

@section('content')
<style>
/* ── Layout ───────────────────────────────────────────────────── */
.zone-section        { margin-bottom: 2rem; }
.zone-header         { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem; }
.zone-title          { font-size: 0.95rem; font-weight: 700; color: var(--text); }
.zone-pill           { font-size: 0.7rem; background: var(--bg); border: 1px solid var(--border);
                        border-radius: 20px; padding: 2px 10px; color: var(--text-muted); }
.zone-pill.active    { background: #eff6ff; border-color: #93c5fd; color: #1d4ed8; }
.colonia-grid        { display: grid; grid-template-columns: repeat(auto-fill, minmax(270px, 1fr)); gap: 0.75rem; }

/* ── Cards ───────────────────────────────────────────────────── */
.colonia-card        { background: var(--card); border: 1px solid var(--border); border-radius: 10px;
                        padding: 1rem 1.1rem; transition: opacity .2s; }
.colonia-card.inactive { opacity: .55; }

.card-top            { display: flex; align-items: flex-start; justify-content: space-between; gap: 0.5rem; margin-bottom: 0.5rem; }
.colonia-name        { font-size: 0.88rem; font-weight: 600; color: var(--text); }
.colonia-cp          { font-size: 0.72rem; color: var(--text-muted); }

/* ── Toggle switch ───────────────────────────────────────────── */
.toggle-form         { display: flex; align-items: center; gap: 0.4rem; flex-shrink: 0; }
.toggle-label        { font-size: 0.7rem; color: var(--text-muted); white-space: nowrap; }
.toggle-wrap         { position: relative; width: 36px; height: 20px; }
.toggle-wrap input   { opacity: 0; width: 0; height: 0; }
.toggle-slider       { position: absolute; inset: 0; background: #d1d5db; border-radius: 20px;
                        cursor: pointer; transition: background .2s; }
.toggle-slider::after { content: ''; position: absolute; left: 3px; top: 3px;
                         width: 14px; height: 14px; border-radius: 50%;
                         background: #fff; transition: transform .2s; }
.toggle-wrap input:checked + .toggle-slider             { background: #1d4ed8; }
.toggle-wrap input:checked + .toggle-slider::after      { transform: translateX(16px); }

/* ── Price data ──────────────────────────────────────────────── */
.price-section       { margin-top: 0.5rem; }
.price-type-label    { font-size: 0.68rem; font-weight: 700; text-transform: uppercase;
                        letter-spacing: .5px; color: var(--text-muted); margin-bottom: 3px; }
.price-row           { display: flex; align-items: center; justify-content: space-between;
                        padding: 2px 0; border-bottom: 1px solid var(--border); font-size: 0.76rem; }
.price-row:last-child { border-bottom: none; }
.price-cat           { color: var(--text-muted); }
.price-val           { font-weight: 600; color: var(--text); }
.no-data             { font-size: 0.75rem; color: var(--text-muted); font-style: italic; padding: 4px 0; }
.period-tag          { display: inline-flex; align-items: center; gap: 4px; font-size: 0.68rem;
                        background: var(--bg); border: 1px solid var(--border); border-radius: 20px;
                        padding: 1px 8px; color: var(--text-muted); margin-bottom: 0.4rem; }
.conf-dot            { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }

/* ── Actions bar ─────────────────────────────────────────────── */
.card-actions        { display: flex; gap: 0.4rem; margin-top: 0.6rem; padding-top: 0.6rem;
                        border-top: 1px solid var(--border); }
.btn-xs              { font-size: 0.72rem; padding: 3px 10px; }
</style>

{{-- ── Page header ──────────────────────────────────────────── --}}
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
        <form method="POST" action="{{ route('admin.market.prices.run') }}"
              onsubmit="return confirm('¿Actualizar precios de todas las colonias ACTIVAS? Esto consumirá créditos de Perplexity y Claude.')">
            @csrf
            <input type="hidden" name="colonia_id" value="all">
            <button type="submit" class="btn btn-primary" style="display:inline-flex;align-items:center;gap:.4rem;">
                ↺ Actualizar colonias activas
            </button>
        </form>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success" style="margin-bottom:1rem;">
    ✓ {{ session('success') }}
    <button class="alert-close" onclick="this.parentElement.remove()">×</button>
</div>
@endif
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

{{-- ── Zonas ─────────────────────────────────────────────────── --}}
@foreach($zones as $zone)
@php
    $total    = $zone->colonias->count();
    $activas  = $zone->colonias->where('is_published', true)->count();
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
            $snaps      = $colonia->snapshots->sortByDesc('period')->groupBy('property_type');
            $latestApt  = $snaps['apartment'] ?? collect();
            $latestHouse= $snaps['house']     ?? collect();
            $period     = $latestApt->first()?->period ?? $latestHouse->first()?->period;
            $confidence = $latestApt->first()?->confidence ?? $latestHouse->first()?->confidence;
            $confColor  = match($confidence) { 'high' => '#16a34a', 'medium' => '#d97706', default => '#94a3b8' };
            $isActive   = $colonia->is_published;
        @endphp
        <div class="colonia-card {{ $isActive ? '' : 'inactive' }}">

            {{-- Header: nombre + toggle --}}
            <div class="card-top">
                <div>
                    <div class="colonia-name">{{ $colonia->name }}</div>
                    @if($colonia->cp)
                    <div class="colonia-cp">CP {{ $colonia->cp }}</div>
                    @endif
                </div>

                {{-- Toggle switch --}}
                <form method="POST" action="{{ route('admin.market.colonias.toggle', $colonia) }}"
                      class="toggle-form" title="{{ $isActive ? 'Desactivar del sitio' : 'Activar en el sitio' }}">
                    @csrf
                    <span class="toggle-label">{{ $isActive ? 'Activa' : 'Oculta' }}</span>
                    <label class="toggle-wrap">
                        <input type="checkbox" {{ $isActive ? 'checked' : '' }}
                               onchange="this.closest('form').submit()">
                        <span class="toggle-slider"></span>
                    </label>
                </form>
            </div>

            {{-- Period tag --}}
            @if($period)
            <span class="period-tag">
                @if($confidence)
                <span class="conf-dot" style="background:{{ $confColor }}"></span>
                @endif
                {{ \Carbon\Carbon::parse($period)->translatedFormat('M Y') }}
            </span>
            @endif

            {{-- Prices --}}
            <div class="price-section">
                @if($latestApt->isNotEmpty())
                <div class="price-type-label" style="margin-top:.25rem;">Departamentos · MXN/m²</div>
                @foreach($latestApt->take(3) as $snap)
                <div class="price-row">
                    <span class="price-cat">{{ ['new'=>'Nuevo','mid'=>'Seminuevo','old'=>'Antiguo'][$snap->age_category] ?? $snap->age_category }}</span>
                    <span class="price-val">${{ number_format($snap->price_m2_avg) }}</span>
                </div>
                @endforeach
                @endif

                @if($latestHouse->isNotEmpty())
                <div class="price-type-label" style="margin-top:.5rem;">Casas · MXN/m²</div>
                @foreach($latestHouse->take(3) as $snap)
                <div class="price-row">
                    <span class="price-cat">{{ ['new'=>'Nuevo','mid'=>'Seminuevo','old'=>'Antiguo'][$snap->age_category] ?? $snap->age_category }}</span>
                    <span class="price-val">${{ number_format($snap->price_m2_avg) }}</span>
                </div>
                @endforeach
                @endif

                @if($latestApt->isEmpty() && $latestHouse->isEmpty())
                <div class="no-data">Sin datos de precios</div>
                @endif
            </div>

            {{-- Action: update prices --}}
            <div class="card-actions">
                <form method="POST" action="{{ route('admin.market.prices.run') }}" style="flex:1;">
                    @csrf
                    <input type="hidden" name="colonia_id" value="{{ $colonia->id }}">
                    <button type="submit" class="btn btn-outline btn-sm btn-xs" style="width:100%;"
                            title="Obtener precios actualizados vía Perplexity + Claude">
                        ↺ Actualizar precios
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endforeach

@endsection
