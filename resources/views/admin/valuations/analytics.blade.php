@extends('layouts.app-sidebar')

@section('content')
<div class="page-header" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
    <div>
        <h1 class="page-title">Analytics de valuaciones</h1>
        <p style="color:#6b7280;font-size:.85rem;margin-top:.25rem;">Precisión del modelo · Cierres registrados · Datos propios</p>
    </div>
    <div style="display:flex;gap:.5rem;">
        <a href="{{ route('admin.valuations.index') }}" class="btn btn-outline">← Volver</a>
    </div>
</div>

{{-- KPI cards --}}
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:1rem;margin-bottom:2rem;">
    @php
        $total      = $closedValuations->count();
        $avgAcc     = $total ? round($closedValuations->avg('accuracy_pct'), 1) : null;
        $within5    = $total ? $closedValuations->filter(fn($v) => abs($v->accuracy_pct) <= 5)->count() : 0;
        $pct5       = $total ? round($within5 / $total * 100) : 0;
    @endphp

    <div class="card" style="text-align:center;padding:1.25rem;">
        <div style="font-size:1.75rem;font-weight:700;">{{ $total }}</div>
        <div style="font-size:.75rem;color:#6b7280;margin-top:.2rem;">Cierres registrados</div>
    </div>
    <div class="card" style="text-align:center;padding:1.25rem;">
        <div style="font-size:1.75rem;font-weight:700;color:{{ $avgAcc !== null && abs($avgAcc) <= 5 ? '#15803d' : '#f59e0b' }};">
            {{ $avgAcc !== null ? ($avgAcc >= 0 ? '+' : '') . $avgAcc . '%' : '—' }}
        </div>
        <div style="font-size:.75rem;color:#6b7280;margin-top:.2rem;">Precisión promedio</div>
    </div>
    <div class="card" style="text-align:center;padding:1.25rem;">
        <div style="font-size:1.75rem;font-weight:700;color:#15803d;">{{ $pct5 }}%</div>
        <div style="font-size:.75rem;color:#6b7280;margin-top:.2rem;">Dentro de ±5%</div>
    </div>
    <div class="card" style="text-align:center;padding:1.25rem;">
        <div style="font-size:1.75rem;font-weight:700;color:#2563eb;">{{ $ownComparables }}</div>
        <div style="font-size:.75rem;color:#6b7280;margin-top:.2rem;">Comparables propios</div>
    </div>
</div>

@if($total === 0)
<div class="card">
    <div class="card-body" style="text-align:center;padding:3rem;color:#9ca3af;">
        <div style="font-size:2rem;margin-bottom:.75rem;">📊</div>
        <p style="font-size:.9rem;">Aún no hay cierres registrados.</p>
        <p style="font-size:.82rem;margin-top:.5rem;">
            Cuando una valuación tenga un precio de cierre registrado (manual o vía Operaciones), aparecerá aquí.
        </p>
    </div>
</div>
@else

{{-- Por zona --}}
@if($zoneStats->count() > 0)
<div class="card" style="margin-bottom:1.5rem;">
    <div class="card-header"><h3 class="card-title">Precisión por zona</h3></div>
    <div class="card-body" style="padding:0;">
        <table style="width:100%;border-collapse:collapse;font-size:.85rem;">
            <thead>
                <tr style="background:#f8fafc;border-bottom:1px solid #e5e7eb;">
                    <th style="padding:.6rem 1rem;text-align:left;color:#6b7280;font-size:.72rem;text-transform:uppercase;">Zona</th>
                    <th style="padding:.6rem 1rem;text-align:center;color:#6b7280;font-size:.72rem;text-transform:uppercase;">Cierres</th>
                    <th style="padding:.6rem 1rem;text-align:center;color:#6b7280;font-size:.72rem;text-transform:uppercase;">Precisión avg</th>
                    <th style="padding:.6rem 1rem;text-align:center;color:#6b7280;font-size:.72rem;text-transform:uppercase;">Sobre predicción</th>
                    <th style="padding:.6rem 1rem;text-align:center;color:#6b7280;font-size:.72rem;text-transform:uppercase;">Bajo predicción</th>
                    <th style="padding:.6rem 1rem;text-align:right;color:#6b7280;font-size:.72rem;text-transform:uppercase;">Precio cierre avg</th>
                </tr>
            </thead>
            <tbody>
                @foreach($zoneStats as $zoneName => $stats)
                <tr style="border-bottom:1px solid #f3f4f6;">
                    <td style="padding:.6rem 1rem;font-weight:600;">{{ $zoneName }}</td>
                    <td style="padding:.6rem 1rem;text-align:center;">{{ $stats['count'] }}</td>
                    <td style="padding:.6rem 1rem;text-align:center;">
                        @php $a = $stats['avg_accuracy']; @endphp
                        <span style="font-weight:700;color:{{ abs($a) <= 5 ? '#15803d' : ($a > 5 ? '#2563eb' : '#dc2626') }};">
                            {{ $a >= 0 ? '+' : '' }}{{ $a }}%
                        </span>
                    </td>
                    <td style="padding:.6rem 1rem;text-align:center;color:#2563eb;">{{ $stats['above_count'] }}</td>
                    <td style="padding:.6rem 1rem;text-align:center;color:#dc2626;">{{ $stats['below_count'] }}</td>
                    <td style="padding:.6rem 1rem;text-align:right;">${{ number_format($stats['avg_sale']) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- Tabla de cierres --}}
<div class="card">
    <div class="card-header"><h3 class="card-title">Cierres registrados</h3></div>
    <div class="card-body" style="padding:0;">
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:.83rem;">
                <thead>
                    <tr style="background:#f8fafc;border-bottom:1px solid #e5e7eb;">
                        <th style="padding:.6rem 1rem;text-align:left;color:#6b7280;font-size:.72rem;text-transform:uppercase;">Inmueble / Colonia</th>
                        <th style="padding:.6rem 1rem;text-align:center;color:#6b7280;font-size:.72rem;text-transform:uppercase;">Tipo</th>
                        <th style="padding:.6rem 1rem;text-align:right;color:#6b7280;font-size:.72rem;text-transform:uppercase;">Sugerido</th>
                        <th style="padding:.6rem 1rem;text-align:right;color:#6b7280;font-size:.72rem;text-transform:uppercase;">Cierre real</th>
                        <th style="padding:.6rem 1rem;text-align:center;color:#6b7280;font-size:.72rem;text-transform:uppercase;">Diferencia</th>
                        <th style="padding:.6rem 1rem;text-align:center;color:#6b7280;font-size:.72rem;text-transform:uppercase;">Fecha</th>
                        <th style="padding:.6rem 1rem;"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($closedValuations as $v)
                    @php $acc = $v->accuracy_pct; @endphp
                    <tr style="border-bottom:1px solid #f3f4f6;" onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background=''">
                        <td style="padding:.6rem 1rem;">
                            <div style="font-weight:600;">{{ $v->property?->title ?? 'Sin inmueble' }}</div>
                            <div style="font-size:.72rem;color:#9ca3af;">{{ $v->colonia?->name ?? $v->input_colonia_raw ?? '—' }}</div>
                        </td>
                        <td style="padding:.6rem 1rem;text-align:center;">{{ $v->type_label }}</td>
                        <td style="padding:.6rem 1rem;text-align:right;">${{ number_format($v->suggested_list_price) }}</td>
                        <td style="padding:.6rem 1rem;text-align:right;font-weight:700;">${{ number_format($v->actual_sale_price) }}</td>
                        <td style="padding:.6rem 1rem;text-align:center;">
                            <span style="font-weight:700;color:{{ abs($acc) <= 5 ? '#15803d' : ($acc > 5 ? '#2563eb' : '#dc2626') }};">
                                {{ $acc >= 0 ? '+' : '' }}{{ $acc }}%
                            </span>
                        </td>
                        <td style="padding:.6rem 1rem;text-align:center;color:#9ca3af;font-size:.78rem;">
                            {{ $v->sale_recorded_at?->format('d M Y') ?? '—' }}
                        </td>
                        <td style="padding:.6rem 1rem;">
                            <a href="{{ route('admin.valuations.show', $v) }}" style="font-size:.78rem;color:#2563eb;">Ver →</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@endif

@endsection
