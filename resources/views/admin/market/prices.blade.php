@extends('layouts.app-sidebar')
@section('title', 'Precios de Mercado')

@section('content')
<style>
.colonia-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:1rem; }
.colonia-card { background:var(--card); border:1px solid var(--border); border-radius:10px; padding:1rem 1.25rem; }
.colonia-name { font-size:0.9rem; font-weight:600; color:var(--text); }
.colonia-zone { font-size:0.75rem; color:var(--text-muted); margin-bottom:0.6rem; }
.price-row  { display:flex; align-items:center; justify-content:space-between; padding:3px 0; border-bottom:1px solid var(--border); font-size:0.78rem; }
.price-row:last-child { border-bottom:none; }
.price-cat  { color:var(--text-muted); }
.price-val  { font-weight:600; color:var(--text); }
.no-data    { font-size:0.78rem; color:var(--text-muted); font-style:italic; padding:0.5rem 0; }
.period-tag { display:inline-block; font-size:0.7rem; background:var(--bg); border:1px solid var(--border); border-radius:20px; padding:1px 8px; color:var(--text-muted); margin-bottom:0.5rem; }
.conf-dot   { display:inline-block; width:7px; height:7px; border-radius:50%; margin-right:4px; vertical-align:middle; }
.run-btn    { display:inline-flex; align-items:center; gap:0.4rem; }
</style>

<div class="page-header" style="align-items:flex-start; flex-wrap:wrap; gap:0.75rem;">
    <div>
        <h2>Precios de Mercado por Colonia</h2>
        <p class="text-muted">
            Datos obtenidos vía Perplexity + Claude.
            @if($lastPeriod)
                Última actualización: <strong>{{ \Carbon\Carbon::parse($lastPeriod)->translatedFormat('F Y') }}</strong>
            @else
                <strong>Sin datos aún.</strong>
            @endif
        </p>
    </div>
    <div style="display:flex; gap:0.5rem; flex-wrap:wrap; margin-left:auto;">
        {{-- Run all --}}
        <form method="POST" action="{{ route('admin.market.prices.run') }}"
              onsubmit="return confirm('¿Actualizar precios de TODAS las colonias? Esto consumirá créditos de Perplexity y Claude.')">
            @csrf
            <input type="hidden" name="colonia_id" value="all">
            <button type="submit" class="btn btn-primary run-btn">
                <x-icon name="refresh-cw" class="w-4 h-4" />
                Actualizar todas las colonias
            </button>
        </form>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success" style="margin-bottom:1.25rem;">
    ✓ {{ session('success') }}
    <button class="alert-close" onclick="this.parentElement.remove()">×</button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger" style="margin-bottom:1.25rem;">
    {{ session('error') }}
    <button class="alert-close" onclick="this.parentElement.remove()">×</button>
</div>
@endif

{{-- Cost note --}}
<div style="background:#fffbeb;border:1px solid #fde68a;border-radius:var(--radius);padding:0.75rem 1rem;font-size:0.82rem;color:#92400e;margin-bottom:1.5rem;">
    ⚠️ Cada actualización llama a <strong>Perplexity + Claude</strong> por colonia × tipo (departamento y casa) = ~4 llamadas por colonia.
    Con 16 colonias son ~64 llamadas. Costo estimado: <strong>~$0.10–0.30 USD por actualización completa</strong>.
</div>

<div class="colonia-grid">
    @foreach($colonias as $colonia)
    @php
        $snaps = $colonia->snapshots->sortByDesc('period')->groupBy('property_type');
        $latestApt  = $snaps['apartment'] ?? collect();
        $latestHouse = $snaps['house'] ?? collect();
        $period = $latestApt->first()?->period ?? $latestHouse->first()?->period;
        $confidence = $latestApt->first()?->confidence ?? $latestHouse->first()?->confidence ?? null;
        $confColor = match($confidence) { 'high' => '#16a34a', 'medium' => '#d97706', default => '#94a3b8' };
    @endphp
    <div class="colonia-card">
        <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:0.5rem; margin-bottom:0.4rem;">
            <div>
                <div class="colonia-name">{{ $colonia->name }}</div>
                <div class="colonia-zone">{{ $colonia->zone->name ?? '—' }}</div>
            </div>
            <form method="POST" action="{{ route('admin.market.prices.run') }}" style="flex-shrink:0;">
                @csrf
                <input type="hidden" name="colonia_id" value="{{ $colonia->id }}">
                <button type="submit" class="btn btn-outline btn-sm" title="Actualizar esta colonia">
                    <x-icon name="refresh-cw" class="w-3 h-3" />
                </button>
            </form>
        </div>

        @if($period)
            <span class="period-tag">
                @if($confidence)
                    <span class="conf-dot" style="background:{{ $confColor }}"></span>
                @endif
                {{ \Carbon\Carbon::parse($period)->translatedFormat('M Y') }}
            </span>
        @endif

        @if($latestApt->isNotEmpty())
        <div style="font-size:0.72rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;">Departamentos · MXN/m²</div>
        @foreach($latestApt->take(3) as $snap)
        <div class="price-row">
            <span class="price-cat">{{ ['new'=>'Nuevo','mid'=>'Seminuevo','old'=>'Viejo'][$snap->age_category] ?? $snap->age_category }}</span>
            <span class="price-val">${{ number_format($snap->price_m2_avg) }}</span>
        </div>
        @endforeach
        @endif

        @if($latestHouse->isNotEmpty())
        <div style="font-size:0.72rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;margin-top:0.5rem;margin-bottom:4px;">Casas · MXN/m²</div>
        @foreach($latestHouse->take(3) as $snap)
        <div class="price-row">
            <span class="price-cat">{{ ['new'=>'Nuevo','mid'=>'Seminuevo','old'=>'Viejo'][$snap->age_category] ?? $snap->age_category }}</span>
            <span class="price-val">${{ number_format($snap->price_m2_avg) }}</span>
        </div>
        @endforeach
        @endif

        @if($latestApt->isEmpty() && $latestHouse->isEmpty())
        <div class="no-data">Sin datos — usa el botón para actualizar</div>
        @endif
    </div>
    @endforeach
</div>

@endsection
