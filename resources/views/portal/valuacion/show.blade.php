@extends('layouts.portal')
@section('title', 'Mi Valuación')

@section('styles')
:root {
    --hdv-navy: #0C1A2E;
    --hdv-blue: #1D4ED8;
    --hdv-blue50: #EFF6FF;
}

/* ── Layout ─────────────────────────────────────────────────────── */
.val-grid {
    display: grid;
    grid-template-columns: 1fr 300px;
    gap: 1.5rem;
    align-items: start;
}
@media (max-width: 768px) { .val-grid { grid-template-columns: 1fr; } }

/* ── Hero price card ─────────────────────────────────────────────── */
.price-hero {
    background: var(--hdv-navy);
    border-radius: 14px;
    padding: 2rem;
    margin-bottom: 1.5rem;
    position: relative;
    overflow: hidden;
}
.price-hero::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    background: var(--hdv-blue);
}
.price-eyebrow {
    font-size: 0.7rem; font-weight: 700; letter-spacing: 1.5px;
    text-transform: uppercase; color: #93C5FD; margin-bottom: 0.4rem;
}
.price-main {
    font-size: 2.8rem; font-weight: 800; color: #fff;
    letter-spacing: -2px; line-height: 1; margin-bottom: 0.3rem;
    font-feature-settings: "tnum";
}
.price-sub  { font-size: 0.85rem; color: #94A3B8; margin-bottom: 1.5rem; }
.price-range-row { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem; }
.price-range-label { font-size: 0.75rem; color: #94A3B8; white-space: nowrap; }
.price-range-track {
    flex: 1; height: 6px; background: rgba(255,255,255,.15);
    border-radius: 3px; position: relative;
}
.price-range-fill {
    position: absolute; top: 0; height: 6px;
    background: rgba(255,255,255,.5); border-radius: 3px;
}
.price-range-dot {
    position: absolute; top: 50%; transform: translate(-50%,-50%);
    width: 12px; height: 12px; border-radius: 50%;
    background: #fff; border: 2px solid var(--hdv-blue);
}
.diag-badge {
    display: inline-flex; align-items: center; gap: 0.4rem;
    font-size: 0.75rem; font-weight: 600;
    padding: 5px 14px; border-radius: 20px; margin-top: 0.75rem;
}

/* ── Section cards ───────────────────────────────────────────────── */
.sec-card {
    background: var(--card); border: 1px solid var(--border);
    border-radius: 12px; margin-bottom: 1.25rem; overflow: hidden;
}
.sec-hd {
    padding: 0.9rem 1.25rem; border-bottom: 1px solid var(--border);
    display: flex; align-items: center; justify-content: space-between;
}
.sec-hd-title { font-size: 0.85rem; font-weight: 700; color: var(--text); }
.sec-body { padding: 1.25rem; }

/* ── Waterfall ───────────────────────────────────────────────────── */
.wf-table { width: 100%; border-collapse: collapse; }
.wf-table td { padding: 0.55rem 1rem; vertical-align: middle; font-size: 0.82rem; }
.wf-table tr:not(:last-child) td { border-bottom: 1px solid var(--border); }
.wf-table tr.wf-base td, .wf-table tr.wf-total td { background: var(--bg); }
.wf-table tr.wf-total td { border-top: 2px solid var(--border); }
.wf-bar-cell { width: 130px; }
.wf-bar-wrap { height: 7px; background: var(--border); border-radius: 4px; overflow: hidden; }
.wf-bar { height: 100%; border-radius: 4px; }
.wf-bar.pos  { background: #10B981; }
.wf-bar.neg  { background: #EF4444; }
.wf-bar.neu  { background: #D1D5DB; }
.wf-pct { width: 60px; text-align: right; font-size: 0.75rem; font-weight: 700; }
.wf-pct.pos { color: #059669; }
.wf-pct.neg { color: #DC2626; }
.wf-pct.neu { color: #9CA3AF; }
.wf-price { width: 100px; text-align: right; font-size: 0.8rem; font-weight: 500; color: var(--text); }
.wf-label { font-weight: 500; }
.wf-explain { font-size: 0.72rem; color: var(--text-muted); margin-top: 2px; }

/* ── AI Narrative ────────────────────────────────────────────────── */
.ai-badge {
    font-size: 0.65rem; background: #EFF6FF; color: var(--hdv-blue);
    padding: 2px 8px; border-radius: 20px; font-weight: 700;
}
.narrative-section { margin-bottom: 1rem; }
.narrative-label {
    font-size: 0.68rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: 0.5px; margin-bottom: 0.4rem;
}
.narrative-text { font-size: 0.86rem; line-height: 1.65; margin: 0; }
.pro-con-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0.85rem; margin-bottom: 1rem; }
@media (max-width: 600px) { .pro-con-grid { grid-template-columns: 1fr; } }
.pro-box {
    background: #F0FDF4; border: 1px solid #BBF7D0;
    border-radius: 8px; padding: 0.85rem 1rem;
}
.con-box {
    background: #FEF2F2; border: 1px solid #FECACA;
    border-radius: 8px; padding: 0.85rem 1rem;
}
.rec-box {
    background: var(--hdv-blue50); border: 1px solid #BFDBFE;
    border-radius: 8px; padding: 0.85rem 1rem; margin-bottom: 1rem;
}
.key-pills { display: flex; flex-wrap: wrap; gap: 0.4rem; }
.key-pill {
    background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 20px;
    padding: 3px 12px; font-size: 0.75rem; color: #1E3A8A; font-weight: 500;
}

/* ── Specs grid ──────────────────────────────────────────────────── */
.specs-grid {
    display: grid; grid-template-columns: repeat(3, 1fr);
}
@media (max-width: 600px) { .specs-grid { grid-template-columns: repeat(2, 1fr); } }
.spec-item {
    padding: 0.55rem 1rem;
    border-bottom: 1px solid var(--border);
    border-right: 1px solid var(--border);
    font-size: 0.82rem;
}
.spec-label { font-size: 0.67rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.3px; color: var(--text-muted); margin-bottom: 2px; }
.spec-value { font-weight: 600; color: var(--text); }

/* ── Price agreement ─────────────────────────────────────────────── */
.agreement-card {
    background: var(--hdv-navy); border-radius: 14px;
    padding: 1.75rem; margin-bottom: 1.25rem;
    position: relative; overflow: hidden;
}
.agreement-card::before {
    content: ''; position: absolute; top: 0; left: 0; right: 0;
    height: 3px; background: #10B981;
}
.agreement-price {
    font-size: 2rem; font-weight: 800; color: #fff;
    letter-spacing: -1px; font-feature-settings: "tnum";
    margin-bottom: 0.25rem;
}
.agreement-label { font-size: 0.75rem; color: #94A3B8; margin-bottom: 1.25rem; }
.btn-confirm {
    display: inline-flex; align-items: center; gap: 0.5rem;
    background: #10B981; color: #fff; font-size: 0.88rem; font-weight: 700;
    padding: 12px 24px; border-radius: 8px; border: none; cursor: pointer;
    width: 100%; justify-content: center; transition: opacity .15s;
    text-decoration: none;
}
.btn-confirm:hover { opacity: .9; }

/* ── Sidebar meta ────────────────────────────────────────────────── */
.meta-row {
    display: flex; justify-content: space-between; align-items: center;
    padding: 0.45rem 1.25rem; border-bottom: 1px solid var(--border);
    font-size: 0.78rem;
}
.meta-row:last-child { border-bottom: none; }
.meta-label { color: var(--text-muted); font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.3px; }
.meta-value { font-weight: 600; color: var(--text); }
@endsection

@section('content')
@php
    $valuation  = $captacion->valuation;
    $n          = $valuation->ai_narrative ?? [];
    $adjs       = $valuation->adjustments->sortBy('sort_order');
    $maxAbs     = $adjs->max(fn($a) => abs((float)$a->adjustment_value)) ?: 1;

    // Rango para la barra visual (posición del punto de precio sugerido)
    $low  = $valuation->total_value_low  ?? 0;
    $mid  = $valuation->total_value_mid  ?? 0;
    $high = $valuation->total_value_high ?? 0;
    $span = $high - $low ?: 1;
    $sugP = $valuation->suggested_list_price ?? $mid;
    $dotPct = round(($sugP - $low) / $span * 100);
    $dotPct = max(0, min(100, $dotPct));

    // Diagnóstico
    $diagColor = match($valuation->diagnosis) {
        'on_market'    => '#10B981',
        'above_market' => '#F59E0B',
        'opportunity'  => '#3B82F6',
        default        => '#94A3B8',
    };
    $diagLabel = match($valuation->diagnosis) {
        'on_market'    => 'Bien posicionado en mercado',
        'above_market' => 'Por encima del mercado',
        'opportunity'  => 'Oportunidad de mercado',
        default        => 'Datos insuficientes',
    };

    // Specs
    $specs = array_filter([
        'Colonia'         => $valuation->colonia?->name ?? $valuation->input_colonia_raw ?? '—',
        'Tipo'            => $valuation->type_label,
        'm² totales'      => number_format($valuation->input_m2_total) . ' m²',
        'm² construidos'  => $valuation->input_m2_const ? number_format($valuation->input_m2_const) . ' m²' : null,
        'Antigüedad'      => ($valuation->input_age_years ?? '—') . ' años',
        'Conservación'    => $valuation->condition_label,
        'Recámaras'       => $valuation->input_bedrooms ?? '—',
        'Baños'           => $valuation->input_bathrooms ?? '—',
        'Estacionamiento' => ($valuation->input_parking ?? '0') . ' cajón(es)',
        'Piso'            => $valuation->input_floor ?? null,
        'Elevador'        => $valuation->input_has_elevator ? 'Sí' : null,
        'Rooftop'         => $valuation->input_has_rooftop ? 'Sí' : null,
        'Balcón'          => $valuation->input_has_balcony ? 'Sí' : null,
        'Cuarto de servicio' => $valuation->input_has_service_room ? 'Sí' : null,
        'Bodega'          => $valuation->input_has_storage ? 'Sí' : null,
    ]);

    // Confidence
    $confLabel = ['high' => 'Alta', 'medium' => 'Media', 'low' => 'Baja'][$valuation->confidence] ?? '—';

    // Price agreement state
    $precioAcordado = $captacion->precio_acordado;
    $precioConfirmado = $captacion->etapa3_completed_at;
@endphp

{{-- ── Page header ──────────────────────────────────────────────── --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;flex-wrap:wrap;gap:.75rem;">
    <div>
        <h2 style="font-size:1.35rem;font-weight:700;">Mi Valuación</h2>
        <p style="color:var(--text-muted);font-size:.82rem;margin-top:2px;">
            {{ $valuation->colonia?->name ?? $valuation->input_colonia_raw ?? 'Sin colonia' }}
            &nbsp;·&nbsp; {{ $valuation->created_at->format('d \d\e F, Y') }}
            &nbsp;·&nbsp; Confianza <strong>{{ $confLabel }}</strong>
        </p>
    </div>
    <a href="{{ route('portal.captacion') }}" style="font-size:.82rem;color:var(--text-muted);">← Volver</a>
</div>

@if(session('success'))
<div style="background:#ECFDF5;border:1px solid #BBF7D0;border-radius:8px;padding:.85rem 1.25rem;font-size:.85rem;color:#065F46;margin-bottom:1.25rem;">
    ✓ {{ session('success') }}
</div>
@endif

{{-- ── Hero: precio sugerido ────────────────────────────────────── --}}
@if($valuation->suggested_list_price)
<div class="price-hero">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:1rem;flex-wrap:wrap;">
        <div>
            <div class="price-eyebrow">Precio sugerido de salida al mercado</div>
            <div class="price-main">${{ number_format($valuation->suggested_list_price) }}</div>
            <div class="price-sub">
                ${{ number_format($valuation->adjusted_price_m2) }}/m²
                &nbsp;·&nbsp; {{ number_format($valuation->effective_m2 ?? $valuation->input_m2_total) }} m²
            </div>
        </div>
        <div style="text-align:right;">
            <div class="diag-badge" style="background:{{ $diagColor }}25;border:1px solid {{ $diagColor }}40;color:{{ $diagColor }};">
                {{ $diagLabel }}
            </div>
        </div>
    </div>

    {{-- Barra de rango --}}
    <div class="price-range-row" style="margin-top:1.5rem;">
        <span class="price-range-label">${{ number_format($low) }}</span>
        <div class="price-range-track">
            <div class="price-range-fill" style="left:0;right:0;"></div>
            <div class="price-range-dot" style="left:{{ $dotPct }}%;"></div>
        </div>
        <span class="price-range-label">${{ number_format($high) }}</span>
    </div>
    <div style="font-size:.7rem;color:#64748B;margin-top:.35rem;text-align:center;">
        Rango estimado del inmueble · El punto indica el precio sugerido de salida
    </div>
</div>
@endif

<div class="val-grid">
{{-- ══════════════ COLUMNA PRINCIPAL ══════════════ --}}
<div>

{{-- ── Factores de ajuste ──────────────────────────────────────── --}}
@if($adjs->isNotEmpty())
<div class="sec-card">
    <div class="sec-hd">
        <span class="sec-hd-title">Cómo calculamos el valor de tu inmueble</span>
        @if($valuation->snapshot)
        <span style="font-size:.7rem;color:var(--text-muted);">
            Base: {{ $valuation->colonia?->name }} · {{ $valuation->snapshot->period->format('M Y') }}
        </span>
        @endif
    </div>
    <table class="wf-table">
        {{-- Base --}}
        <tr class="wf-base">
            <td class="wf-label">
                Precio base de la zona
                @if($valuation->colonia)
                <div class="wf-explain">{{ $valuation->colonia->name }}
                    @if($valuation->snapshot) · {{ $valuation->snapshot->age_label }}@endif
                </div>
                @endif
            </td>
            <td class="wf-bar-cell"><div class="wf-bar-wrap"><div class="wf-bar neu" style="width:100%;"></div></div></td>
            <td class="wf-pct neu">Base</td>
            <td class="wf-price">${{ number_format($valuation->base_price_m2) }}/m²</td>
        </tr>

        {{-- Ajustes --}}
        @foreach($adjs as $adj)
        @php
            $isPos = $adj->is_positive;
            $isNeu = $adj->is_neutral;
            $cls   = $isNeu ? 'neu' : ($isPos ? 'pos' : 'neg');
            $w     = $isNeu ? 100 : min(100, round(abs((float)$adj->adjustment_value) / $maxAbs * 100));
        @endphp
        <tr>
            <td>
                <div class="wf-label">{{ $adj->factor_label }}</div>
                @if($adj->explanation)<div class="wf-explain">{{ $adj->explanation }}</div>@endif
            </td>
            <td class="wf-bar-cell"><div class="wf-bar-wrap"><div class="wf-bar {{ $cls }}" style="width:{{ $w }}%;"></div></div></td>
            <td class="wf-pct {{ $cls }}">{{ $adj->formatted_value }}</td>
            <td class="wf-price">${{ number_format($adj->price_after) }}/m²</td>
        </tr>
        @endforeach

        {{-- Total --}}
        @php $totalPct = $valuation->base_price_m2 > 0 ? round((($valuation->adjusted_price_m2 - $valuation->base_price_m2) / $valuation->base_price_m2) * 100, 1) : 0; @endphp
        <tr class="wf-total">
            <td class="wf-label" style="font-weight:700;">Precio m² final de tu inmueble</td>
            <td class="wf-bar-cell"></td>
            <td class="wf-pct {{ $totalPct >= 0 ? 'pos' : 'neg' }}">{{ ($totalPct >= 0 ? '+' : '') . $totalPct }}%</td>
            <td class="wf-price" style="font-weight:800;font-size:.9rem;">${{ number_format($valuation->adjusted_price_m2) }}/m²</td>
        </tr>
    </table>
</div>
@endif

{{-- ── Análisis profesional IA ─────────────────────────────────── --}}
@if(!empty($n))
<div class="sec-card" style="border-left:3px solid var(--hdv-blue);">
    <div class="sec-hd">
        <span class="sec-hd-title">Análisis del mercado</span>
        <span class="ai-badge">Análisis IA</span>
    </div>
    <div class="sec-body">

        {{-- Contexto de mercado --}}
        @if(!empty($n['market_context']))
        <div class="narrative-section">
            <div class="narrative-label" style="color:var(--text-muted);">Mercado · {{ $valuation->colonia?->name ?? 'Tu zona' }}</div>
            <p class="narrative-text" style="color:var(--text);">{{ $n['market_context'] }}</p>
        </div>
        @endif

        {{-- Pros y contras --}}
        @if(!empty($n['property_strengths']) || !empty($n['property_risks']))
        <div class="pro-con-grid">
            @if(!empty($n['property_strengths']))
            <div class="pro-box">
                <div class="narrative-label" style="color:#15803D;">✓ Fortalezas de tu inmueble</div>
                <p class="narrative-text" style="color:#14532D;font-size:.83rem;">{{ $n['property_strengths'] }}</p>
            </div>
            @endif
            @if(!empty($n['property_risks']))
            <div class="con-box">
                <div class="narrative-label" style="color:#DC2626;">⚠ Puntos a considerar</div>
                <p class="narrative-text" style="color:#7F1D1D;font-size:.83rem;">{{ $n['property_risks'] }}</p>
            </div>
            @endif
        </div>
        @endif

        {{-- Recomendación --}}
        @if(!empty($n['recommendation']))
        <div class="rec-box">
            <div class="narrative-label" style="color:var(--hdv-blue);">Recomendación de Home del Valle</div>
            <p class="narrative-text" style="color:#1E3A8A;">{{ $n['recommendation'] }}</p>
        </div>
        @endif

        {{-- Factores clave --}}
        @if(!empty($n['key_factors']) && is_array($n['key_factors']))
        <div>
            <div class="narrative-label" style="color:var(--text-muted);margin-bottom:.5rem;">Factores que más pesan en el valor</div>
            <div class="key-pills">
                @foreach($n['key_factors'] as $f)
                <span class="key-pill">{{ $f }}</span>
                @endforeach
            </div>
        </div>
        @endif

    </div>
</div>
@endif

{{-- ── Características del inmueble ───────────────────────────── --}}
<div class="sec-card">
    <div class="sec-hd">
        <span class="sec-hd-title">Características de tu inmueble</span>
    </div>
    <div class="specs-grid">
        @foreach($specs as $lbl => $val)
        <div class="spec-item">
            <div class="spec-label">{{ $lbl }}</div>
            <div class="spec-value">{{ $val }}</div>
        </div>
        @endforeach
    </div>
    @if($valuation->input_notes)
    <div style="padding:.75rem 1.25rem;font-size:.83rem;color:var(--text-muted);border-top:1px solid var(--border);">
        <strong style="color:var(--text);">Notas:</strong> {{ $valuation->input_notes }}
    </div>
    @endif
</div>

</div>{{-- /columna principal --}}

{{-- ══════════════ SIDEBAR ══════════════ --}}
<div>

{{-- ── Acuerdo de precio ────────────────────────────────────────── --}}
@if($precioAcordado)
    @if($precioConfirmado)
    {{-- YA CONFIRMADO --}}
    <div class="agreement-card" style="background:#064E3B;">
        <div class="price-eyebrow" style="color:#6EE7B7;">Precio de venta confirmado</div>
        <div class="agreement-price">${{ number_format($precioAcordado) }}</div>
        <div class="agreement-label">Confirmado el {{ $captacion->etapa3_completed_at->format('d/m/Y') }}</div>
        <div style="font-size:.8rem;color:#6EE7B7;background:rgba(255,255,255,.07);border-radius:8px;padding:.75rem 1rem;">
            ✓ Tu asesor está preparando el contrato de exclusiva. Lo recibirás pronto por correo para firma digital.
        </div>
    </div>
    @else
    {{-- PENDIENTE DE CONFIRMAR --}}
    <div class="agreement-card">
        <div class="price-eyebrow">Tu asesor propone este precio de salida</div>
        <div class="agreement-price">${{ number_format($precioAcordado) }}</div>
        <div class="agreement-label">Precio de venta al mercado · MXN</div>
        <p style="font-size:.82rem;color:#94A3B8;margin-bottom:1.25rem;line-height:1.5;">
            Basado en el análisis de mercado y las características de tu inmueble. Cuando confirmes, tu asesor generará el contrato de exclusiva.
        </p>
        <form method="POST" action="{{ route('portal.valuacion.confirm-price') }}">
            @csrf
            <button type="submit" class="btn-confirm">
                ✓ &nbsp;Acepto este precio de venta
            </button>
        </form>
        <p style="font-size:.72rem;color:#64748B;margin-top:.75rem;text-align:center;">
            Si tienes dudas, escríbele a tu asesor antes de confirmar.
        </p>
    </div>
    @endif
@else
{{-- SIN PRECIO AÚN --}}
<div class="sec-card" style="margin-bottom:1.25rem;">
    <div class="sec-hd"><span class="sec-hd-title">Precio de venta</span></div>
    <div class="sec-body" style="text-align:center;padding:2rem 1.25rem;">
        <div style="font-size:1.5rem;margin-bottom:.5rem;">⏳</div>
        <p style="font-size:.85rem;color:var(--text-muted);line-height:1.5;">
            Tu asesor revisará esta valuación contigo y te propondrá el precio de salida ideal. Aparecerá aquí para que lo confirmes.
        </p>
    </div>
</div>
@endif

{{-- ── Detalles del cálculo ─────────────────────────────────────── --}}
<div class="sec-card">
    <div class="sec-hd"><span class="sec-hd-title">Detalles del cálculo</span></div>
    <div>
        @foreach([
            'Precio base m²'    => '$' . number_format($valuation->base_price_m2),
            'Precio ajustado m²'=> '$' . number_format($valuation->adjusted_price_m2),
            'Rango mínimo'      => '$' . number_format($valuation->total_value_low),
            'Rango medio'       => '$' . number_format($valuation->total_value_mid),
            'Rango máximo'      => '$' . number_format($valuation->total_value_high),
            'Confianza'         => $confLabel,
            'Generada'          => $valuation->created_at->format('d/m/Y'),
        ] as $lbl => $val)
        <div class="meta-row">
            <span class="meta-label">{{ $lbl }}</span>
            <span class="meta-value">{{ $val }}</span>
        </div>
        @endforeach
    </div>
</div>

{{-- ── PDF ─────────────────────────────────────────────────────── --}}
<div style="margin-top:1rem;text-align:center;">
    <p style="font-size:.78rem;color:var(--text-muted);">¿Quieres el análisis completo en PDF?</p>
    <p style="font-size:.78rem;color:var(--text-muted);">Pídele a tu asesor que te lo envíe.</p>
</div>

</div>{{-- /sidebar --}}
</div>{{-- /val-grid --}}
@endsection
