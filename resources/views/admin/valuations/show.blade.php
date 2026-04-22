@extends('layouts.app-sidebar')
@section('title', 'Opinión de Valor')

@section('styles')
/* ── Waterfall ── */
.wf-table { width:100%; border-collapse:collapse; }
.wf-table td { padding:.55rem .75rem; vertical-align:middle; font-size:.83rem; }
.wf-table tr:not(:last-child) td { border-bottom:1px solid #f0f2f5; }
.wf-bar-wrap { width:140px; }
.wf-bar {
    height:7px; border-radius:4px; min-width:2px;
    transition:width .3s;
}
.wf-bar.positive { background:#10b981; }
.wf-bar.negative { background:#ef4444; }
.wf-bar.neutral  { background:#e5e7eb; width:100%!important; }
.wf-pct { font-size:.75rem; font-weight:600; min-width:52px; text-align:right; }
.wf-pct.positive { color:#16a34a; }
.wf-pct.negative { color:#dc2626; }
.wf-pct.neutral  { color:#9ca3af; }
.wf-price { font-size:.82rem; color:#374151; font-weight:500; min-width:90px; text-align:right; }

/* ── Resumen ── */
.summary-card {
    border-radius:10px; padding:1.5rem;
    background:linear-gradient(135deg,#1e3a5f,#2563eb);
    color:#fff; margin-bottom:1.5rem;
}
.summary-card .price-main {
    font-size:2rem; font-weight:700; letter-spacing:-1px; line-height:1;
}
.summary-card .price-sub { font-size:.85rem; opacity:.75; margin-top:.3rem; }
.summary-card .range-bar {
    display:flex; align-items:center; gap:.5rem;
    margin-top:1rem; font-size:.8rem; opacity:.85;
}
.range-track {
    flex:1; height:4px; background:rgba(255,255,255,.2); border-radius:2px; position:relative;
}
.range-fill { position:absolute; top:0; height:4px; background:rgba(255,255,255,.7); border-radius:2px; }
@endsection

@section('content')
<div class="page-header">
    <div>
        <h2>Opinión de Valor</h2>
        <p class="text-muted" style="font-size:.83rem;margin-top:4px;">
            @if($valuation->property)
                {{ Str::limit($valuation->property->title, 60) }}
                &nbsp;·&nbsp;
            @endif
            {{ $valuation->colonia?->name ?? $valuation->input_colonia_raw ?? 'Sin colonia' }}
            &nbsp;·&nbsp; {{ $valuation->created_at->format('d/m/Y') }}
        </p>
    </div>
    <div style="display:flex;gap:.5rem;">
        <a href="{{ route('admin.valuations.edit', $valuation) }}" class="btn btn-outline">Editar datos</a>
        <a href="{{ route('admin.valuations.index') }}" class="btn btn-outline">← Volver</a>
    </div>
</div>

@if($valuation->diagnosis === 'insufficient_data')
<div class="alert alert-error" style="margin-bottom:1.5rem;">
    <strong>Sin datos de mercado para esta colonia.</strong>
    No hay precios registrados para calcular la valuación.
    Agrega precios al módulo de mercado o selecciona otra colonia.
</div>
@endif

<div style="display:grid;grid-template-columns:1fr 280px;gap:1.5rem;align-items:start;">

    {{-- ══ COLUMNA PRINCIPAL ══ --}}
    <div>

        {{-- Resumen ejecutivo --}}
        @if($valuation->suggested_list_price)
        <div class="summary-card">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:1rem;flex-wrap:wrap;">
                <div>
                    <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:1px;opacity:.65;margin-bottom:.4rem;">
                        Precio sugerido de salida
                    </div>
                    <div class="price-main">${{ number_format($valuation->suggested_list_price) }}</div>
                    <div class="price-sub">
                        ${{ number_format($valuation->adjusted_price_m2) }}/m²
                        · {{ number_format($valuation->input_m2_const ?? $valuation->input_m2_total) }}m²
                    </div>
                </div>
                <div style="text-align:right;">
                    <div style="font-size:.7rem;opacity:.65;text-transform:uppercase;letter-spacing:.5px;margin-bottom:.4rem;">
                        Diagnóstico
                    </div>
                    @php
                        $diagIcon = match($valuation->diagnosis) {
                            'on_market'    => '●',
                            'above_market' => '▲',
                            'opportunity'  => '▼',
                            default        => '?',
                        };
                    @endphp
                    <div style="font-size:.95rem;font-weight:600;">
                        {{ $diagIcon }} {{ $valuation->diagnosis_label }}
                    </div>
                    <div style="font-size:.75rem;opacity:.65;margin-top:.25rem;">
                        Confianza: {{ ['high'=>'Alta','medium'=>'Media','low'=>'Baja'][$valuation->confidence] ?? '—' }}
                    </div>
                </div>
            </div>

            {{-- Barra de rango --}}
            <div class="range-bar">
                <span>${{ number_format($valuation->total_value_low) }}</span>
                <div class="range-track">
                    <div class="range-fill" style="left:0;right:0;"></div>
                </div>
                <span>${{ number_format($valuation->total_value_high) }}</span>
            </div>
            <div style="font-size:.72rem;opacity:.55;margin-top:.3rem;text-align:center;">
                Rango estimado del inmueble
            </div>
        </div>
        @endif

        {{-- Waterfall de ajustes --}}
        @if($valuation->adjustments->isNotEmpty())
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Factores de ajuste</h3>
                @if($valuation->snapshot)
                <span style="font-size:.73rem;color:#6b7280;">
                    Base: {{ $valuation->colonia?->name }}
                    · {{ $valuation->snapshot->period->format('M Y') }}
                    · <span class="badge badge-{{ $valuation->snapshot->confidence_color }}" style="font-size:.65rem;">
                        {{ $valuation->snapshot->confidence_label }}
                    </span>
                </span>
                @endif
            </div>
            <div class="card-body" style="padding:0;">
                <table class="wf-table">
                    {{-- Fila base --}}
                    <tr style="background:#f8fafc;">
                        <td style="font-weight:600;color:#374151;">
                            Precio base zona
                            @if($valuation->colonia)
                            <div style="font-size:.72rem;color:#6b7280;font-weight:400;">
                                {{ $valuation->colonia->name }}
                                @if($valuation->snapshot)
                                    · {{ $valuation->snapshot->age_label }}
                                @endif
                            </div>
                            @endif
                        </td>
                        <td class="wf-bar-wrap"></td>
                        <td class="wf-pct neutral">—</td>
                        <td class="wf-price">${{ number_format($valuation->base_price_m2) }}/m²</td>
                    </tr>

                    {{-- Factores --}}
                    @php $maxAbs = $valuation->adjustments->max(fn($a) => abs($a->adjustment_value)) ?: 1; @endphp
                    @foreach($valuation->adjustments as $adj)
                    @php
                        $isPos     = $adj->is_positive;
                        $isNeutral = $adj->is_neutral;
                        $barWidth  = $isNeutral ? 100 : min(100, round(abs($adj->adjustment_value) / $maxAbs * 100));
                        $barClass  = $isNeutral ? 'neutral' : ($isPos ? 'positive' : 'negative');
                    @endphp
                    <tr>
                        <td>
                            <div style="font-weight:500;">{{ $adj->factor_label }}</div>
                            @if($adj->explanation)
                            <div style="font-size:.72rem;color:#9ca3af;margin-top:1px;">{{ $adj->explanation }}</div>
                            @endif
                        </td>
                        <td class="wf-bar-wrap">
                            <div class="wf-bar {{ $barClass }}" style="width:{{ $barWidth }}%;"></div>
                        </td>
                        <td class="wf-pct {{ $barClass }}">{{ $adj->formatted_value }}</td>
                        <td class="wf-price">${{ number_format($adj->price_after) }}/m²</td>
                    </tr>
                    @endforeach

                    {{-- Total --}}
                    <tr style="background:#f8fafc;border-top:2px solid #e5e7eb;">
                        <td style="font-weight:700;color:#111827;">Precio m² ajustado</td>
                        <td></td>
                        @php $totalPct = round((($valuation->adjusted_price_m2 - $valuation->base_price_m2) / $valuation->base_price_m2) * 100, 1); @endphp
                        <td class="wf-pct {{ $totalPct >= 0 ? 'positive' : 'negative' }}">
                            {{ ($totalPct >= 0 ? '+' : '') . $totalPct }}%
                        </td>
                        <td class="wf-price" style="font-weight:700;font-size:.9rem;">
                            ${{ number_format($valuation->adjusted_price_m2) }}/m²
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        @endif

        {{-- Análisis profesional IA --}}
        @if($valuation->ai_narrative)
        @php $n = $valuation->ai_narrative; @endphp
        <div class="card" style="border-left:3px solid #2563eb;">
            <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
                <h3 class="card-title">Análisis profesional IA</h3>
                <span style="font-size:.68rem;background:#eff6ff;color:#2563eb;padding:.2rem .6rem;border-radius:20px;font-weight:600;">Claude</span>
            </div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:1.25rem;">

                @if(!empty($n['market_context']))
                <div>
                    <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#9ca3af;margin-bottom:.35rem;">
                        Mercado · {{ $valuation->colonia?->name ?? 'Zona' }}
                    </div>
                    <p style="font-size:.875rem;color:#374151;line-height:1.65;margin:0;">{{ $n['market_context'] }}</p>
                </div>
                @endif

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    @if(!empty($n['property_strengths']))
                    <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:7px;padding:.85rem 1rem;">
                        <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#15803d;margin-bottom:.35rem;">Fortalezas</div>
                        <p style="font-size:.83rem;color:#14532d;line-height:1.6;margin:0;">{{ $n['property_strengths'] }}</p>
                    </div>
                    @endif
                    @if(!empty($n['property_risks']))
                    <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:7px;padding:.85rem 1rem;">
                        <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#dc2626;margin-bottom:.35rem;">Riesgo principal</div>
                        <p style="font-size:.83rem;color:#7f1d1d;line-height:1.6;margin:0;">{{ $n['property_risks'] }}</p>
                    </div>
                    @endif
                </div>

                @if(!empty($n['recommendation']))
                <div style="background:#f0f7ff;border:1px solid #bfdbfe;border-radius:7px;padding:.85rem 1rem;">
                    <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#2563eb;margin-bottom:.35rem;">Recomendación comercial</div>
                    <p style="font-size:.875rem;color:#1e3a8a;line-height:1.65;margin:0;">{{ $n['recommendation'] }}</p>
                </div>
                @endif

                @if(!empty($n['key_factors']) && is_array($n['key_factors']))
                <div>
                    <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#9ca3af;margin-bottom:.5rem;">Factores clave</div>
                    <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
                        @foreach($n['key_factors'] as $f)
                        <span style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:20px;padding:.25rem .75rem;font-size:.78rem;color:#1e3a8a;font-weight:500;">{{ $f }}</span>
                        @endforeach
                    </div>
                </div>
                @endif

            </div>
        </div>
        @endif

        {{-- Características del inmueble --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Características capturadas</h3>
                <a href="{{ route('admin.valuations.edit', $valuation) }}" style="font-size:.78rem;color:#2563eb;">Editar</a>
            </div>
            <div class="card-body" style="padding:0;">
                @php
                $chars = [
                    'Colonia'        => $valuation->colonia?->name ?? $valuation->input_colonia_raw ?? '—',
                    'Tipo'           => $valuation->type_label,
                    'm² totales'     => number_format($valuation->input_m2_total) . ' m²',
                    'm² construcción'=> $valuation->input_m2_const ? number_format($valuation->input_m2_const) . ' m²' : '—',
                    'Antigüedad'     => $valuation->input_age_years . ' años',
                    'Conservación'   => $valuation->condition_label,
                    'Recámaras'      => $valuation->input_bedrooms,
                    'Baños'          => $valuation->input_bathrooms,
                    'Estacionamiento'=> $valuation->input_parking . ' cajón(es)',
                    'Piso'           => $valuation->input_floor ?? '—',
                    'Elevador'       => $valuation->input_has_elevator ? 'Sí' : 'No',
                    'Rooftop'        => $valuation->input_has_rooftop ? 'Sí' : '—',
                    'Balcón'         => $valuation->input_has_balcony ? 'Sí' : '—',
                    'Cuarto servicio'=> $valuation->input_has_service_room ? 'Sí' : '—',
                    'Bodega'         => $valuation->input_has_storage ? 'Sí' : '—',
                ];
                @endphp
                <div style="display:grid;grid-template-columns:repeat(3,1fr);">
                    @foreach($chars as $lbl => $val)
                    <div style="padding:.5rem 1.25rem;border-bottom:1px solid #f0f2f5;border-right:1px solid #f0f2f5;font-size:.82rem;">
                        <div style="color:#9ca3af;font-size:.7rem;text-transform:uppercase;letter-spacing:.3px;font-weight:600;">{{ $lbl }}</div>
                        <div style="color:#1f2937;font-weight:500;margin-top:2px;">{{ $val }}</div>
                    </div>
                    @endforeach
                </div>
                @if($valuation->input_notes)
                <div style="padding:.75rem 1.25rem;font-size:.83rem;color:#6b7280;border-top:1px solid #f0f2f5;">
                    <strong style="color:#374151;">Notas:</strong> {{ $valuation->input_notes }}
                </div>
                @endif
            </div>
        </div>

    </div>{{-- /columna principal --}}

    {{-- ══ SIDEBAR ══ --}}
    <div style="display:flex;flex-direction:column;gap:1.5rem;position:sticky;top:72px;">

        {{-- Acciones --}}
        <div class="card">
            <div class="card-header"><h3 class="card-title">Acciones</h3></div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:.5rem;">

                <a href="{{ route('admin.valuations.edit', $valuation) }}" class="btn btn-primary" style="width:100%;text-align:center;">
                    🔄 Recalcular
                </a>

                <hr style="border:none;border-top:1px solid #e5e7eb;margin:.1rem 0;">

                {{-- Cambio de estado --}}
                <form method="POST" action="{{ route('admin.valuations.status', $valuation) }}">
                    @csrf @method('PATCH')
                    <div style="display:flex;gap:.4rem;">
                        @foreach(['draft'=>'Borrador','final'=>'Final','delivered'=>'Entregada'] as $s => $lbl)
                        <button type="submit" name="status" value="{{ $s }}"
                                class="btn btn-sm {{ $valuation->status === $s ? 'btn-primary' : 'btn-outline' }}"
                                style="flex:1;">
                            {{ $lbl }}
                        </button>
                        @endforeach
                    </div>
                </form>

                <hr style="border:none;border-top:1px solid #e5e7eb;margin:.1rem 0;">

                <a href="{{ route('admin.valuations.pdf', $valuation) }}"
                   target="_blank"
                   class="btn btn-outline" style="width:100%;text-align:center;">
                    📄 Generar PDF
                </a>

                @if($valuation->property)
                <a href="{{ route('properties.show', $valuation->property) }}" class="btn btn-outline" style="width:100%;text-align:center;">
                    🏠 Ver inmueble
                </a>
                @endif

                <form method="POST" action="{{ route('admin.valuations.destroy', $valuation) }}"
                      onsubmit="return confirm('¿Eliminar esta valuación?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-outline" style="width:100%;color:#dc2626;border-color:#fecaca;">
                        Eliminar
                    </button>
                </form>
            </div>
        </div>

        {{-- Registrar cierre real --}}
        @if(!$valuation->actual_sale_price && $valuation->suggested_list_price)
        <div class="card">
            <div class="card-header"><h3 class="card-title">Registrar precio de cierre</h3></div>
            <div class="card-body">
                <p style="font-size:.8rem;color:#6b7280;margin-bottom:.85rem;">
                    Cuando el inmueble se venda, registra el precio real para calibrar el modelo.
                </p>
                <form method="POST" action="{{ route('admin.valuations.record-sale', $valuation) }}"
                      style="display:flex;flex-direction:column;gap:.65rem;">
                    @csrf
                    <div>
                        <label style="display:block;font-size:.78rem;font-weight:600;color:#374151;margin-bottom:.25rem;">
                            Precio de cierre (MXN)
                        </label>
                        <input type="number" name="actual_sale_price" min="1" step="1000"
                               placeholder="{{ number_format($valuation->suggested_list_price, 0, '.', '') }}"
                               style="width:100%;padding:.45rem .7rem;border:1px solid #d1d5db;border-radius:6px;font-size:.85rem;box-sizing:border-box;"
                               required>
                    </div>
                    <div>
                        <label style="display:block;font-size:.78rem;font-weight:600;color:#374151;margin-bottom:.25rem;">
                            Fecha de cierre
                        </label>
                        <input type="date" name="closed_at" value="{{ now()->toDateString() }}"
                               style="width:100%;padding:.45rem .7rem;border:1px solid #d1d5db;border-radius:6px;font-size:.85rem;box-sizing:border-box;">
                    </div>
                    <button type="submit" class="btn btn-primary" style="width:100%;">
                        Registrar cierre →
                    </button>
                </form>
            </div>
        </div>
        @elseif($valuation->actual_sale_price)
        <div class="card">
            <div class="card-header"><h3 class="card-title">Cierre registrado</h3></div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:.5rem;">
                <div style="display:flex;justify-content:space-between;font-size:.85rem;">
                    <span style="color:#6b7280;">Precio de cierre</span>
                    <strong>${{ number_format($valuation->actual_sale_price) }}</strong>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:.85rem;">
                    <span style="color:#6b7280;">Precio sugerido</span>
                    <span>${{ number_format($valuation->suggested_list_price) }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:.85rem;">
                    <span style="color:#6b7280;">Precisión</span>
                    @php $acc = $valuation->accuracy_pct; @endphp
                    <strong style="color:{{ $acc >= -5 && $acc <= 5 ? '#15803d' : ($acc > 5 ? '#2563eb' : '#dc2626') }};">
                        {{ $acc >= 0 ? '+' : '' }}{{ $acc }}%
                    </strong>
                </div>
                @if($valuation->sale_recorded_at)
                <div style="font-size:.72rem;color:#9ca3af;margin-top:.25rem;">
                    Registrado {{ $valuation->sale_recorded_at->format('d M Y') }}
                </div>
                @endif
            </div>
        </div>
        @endif

        {{-- Detalles de cálculo --}}
        <div class="card">
            <div class="card-header"><h3 class="card-title">Detalles del cálculo</h3></div>
            <div class="card-body" style="padding:0;">
                @php
                $meta = [
                    'Precio base m²'     => '$' . number_format($valuation->base_price_m2),
                    'Precio ajustado m²' => '$' . number_format($valuation->adjusted_price_m2),
                    'Total bajo'         => '$' . number_format($valuation->total_value_low),
                    'Total medio'        => '$' . number_format($valuation->total_value_mid),
                    'Total alto'         => '$' . number_format($valuation->total_value_high),
                    'Fuente datos'       => $valuation->used_perplexity ? 'Perplexity + Interno' : 'Interno',
                    'Estado'             => $valuation->status_label,
                    'Creada por'         => $valuation->creator?->name ?? '—',
                    'Fecha'              => $valuation->created_at->format('d/m/Y H:i'),
                ];
                @endphp
                @foreach($meta as $lbl => $val)
                <div style="display:flex;justify-content:space-between;padding:.45rem 1rem;border-bottom:1px solid #f0f2f5;font-size:.78rem;">
                    <span style="color:#9ca3af;font-weight:600;text-transform:uppercase;font-size:.68rem;letter-spacing:.3px;">{{ $lbl }}</span>
                    <span style="color:#1f2937;font-weight:500;">{{ $val }}</span>
                </div>
                @endforeach
            </div>
        </div>

    </div>{{-- /sidebar --}}
</div>
@endsection
