@extends('layouts.app-sidebar')
@section('title', 'Atribución de Origen')

@section('styles')
<style>
.an-stats { display: flex; flex-direction: row; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 1.5rem; }
.an-stat {
    flex: 1; min-width: 160px; background: var(--card); border: 1px solid var(--border);
    border-radius: 12px; padding: 1rem 1.25rem;
}
.an-stat-val { font-size: 1.6rem; font-weight: 700; line-height: 1; color: var(--text); }
.an-stat-label { font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem; }
.an-card { background: var(--card); border: 1px solid var(--border); border-radius: 12px; padding: 1.25rem; margin-bottom: 1.5rem; }
.an-card h3 { font-size: 0.95rem; font-weight: 700; margin-bottom: 1rem; }
.an-range { display: flex; gap: 0.4rem; }
.an-range a {
    font-size: 0.78rem; padding: 0.35rem 0.75rem; border-radius: 8px; border: 1px solid var(--border);
    color: var(--text-muted); text-decoration: none;
}
.an-range a.active { background: var(--primary); border-color: var(--primary); color: #fff; font-weight: 600; }
.an-table { width: 100%; border-collapse: collapse; font-size: 0.82rem; }
.an-table th { text-align: left; padding: 0.6rem 0.5rem; color: var(--text-muted); font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid var(--border); }
.an-table td { padding: 0.6rem 0.5rem; border-bottom: 1px solid var(--border); }
.an-table tr:last-child td { border-bottom: none; }
.an-bar-track { background: var(--bg,#f1f5f9); border-radius: 4px; height: 8px; overflow: hidden; width: 100%; margin-top: 4px; }
.an-bar-fill { background: var(--primary); height: 100%; border-radius: 4px; }
</style>
@endsection

@section('content')
<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;margin-bottom:1.25rem;">
    <div>
        <h2 style="margin:0;font-size:1.3rem;">&#127919; Atribución de Origen</h2>
        <p style="margin:0.2rem 0 0;color:var(--text-muted);font-size:0.85rem;">De dónde llegan los leads — blog, testimonios, landing pages, etc.</p>
    </div>
    <div class="an-range">
        @foreach([7 => '7 días', 30 => '30 días', 90 => '90 días'] as $d => $label)
        <a href="{{ route('admin.attribution', ['range' => $d]) }}" class="{{ $rangeDays === $d ? 'active' : '' }}">{{ $label }}</a>
        @endforeach
    </div>
</div>

<div class="an-stats">
    <div class="an-stat">
        <div class="an-stat-val">{{ number_format($totalConversions) }}</div>
        <div class="an-stat-label">Conversiones totales ({{ $rangeDays }} días)</div>
    </div>
    <div class="an-stat">
        <div class="an-stat-val">{{ number_format($attributedConversions) }}</div>
        <div class="an-stat-label">Con origen identificado</div>
    </div>
    <div class="an-stat">
        <div class="an-stat-val">{{ $totalConversions > 0 ? round($attributedConversions / $totalConversions * 100) : 0 }}%</div>
        <div class="an-stat-label">Tasa de atribución</div>
    </div>
</div>

<div class="an-card">
    <h3>Conversiones por página de entrada</h3>
    @if($byLabel->isEmpty())
    <p style="color:var(--text-muted);font-size:0.85rem;">Sin conversiones registradas en este rango todavía.</p>
    @else
    <table class="an-table">
        <thead><tr><th>Página</th><th style="width:120px;">Conversiones</th><th style="width:200px;"></th></tr></thead>
        <tbody>
            @php $maxLabel = $byLabel->max('count') ?: 1; @endphp
            @foreach($byLabel as $row)
            <tr>
                <td>{{ $row['label'] }}</td>
                <td style="font-weight:700;">{{ number_format($row['count']) }}</td>
                <td><div class="an-bar-track"><div class="an-bar-fill" style="width:{{ round($row['count'] / $maxLabel * 100) }}%;"></div></div></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>

<div class="an-card">
    <h3>Fuente de tráfico (UTM o referrer)</h3>
    @if($bySource->isEmpty())
    <p style="color:var(--text-muted);font-size:0.85rem;">Sin conversiones registradas en este rango todavía.</p>
    @else
    <table class="an-table">
        <thead><tr><th>Fuente</th><th style="width:120px;">Conversiones</th></tr></thead>
        <tbody>
            @foreach($bySource as $row)
            <tr>
                <td>{{ $row['source'] }}</td>
                <td style="font-weight:700;">{{ number_format($row['count']) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>

<div class="an-card">
    <h3>&#128200; Blog: qué artículos convierten</h3>
    @if($byPost->isEmpty())
    <p style="color:var(--text-muted);font-size:0.85rem;">Sin conversiones atribuidas a un artículo de blog en este rango todavía.</p>
    @else
    <table class="an-table">
        <thead><tr><th>Artículo</th><th style="width:130px;">Vistas totales</th><th style="width:130px;">Conversiones ({{ $rangeDays }}d)</th></tr></thead>
        <tbody>
            @foreach($byPost as $row)
            <tr>
                <td>{{ $row['title'] }}</td>
                <td>{{ number_format($row['views_count']) }}</td>
                <td style="font-weight:700;">{{ number_format($row['conversions']) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <p style="font-size:0.72rem;color:var(--text-muted);margin-top:0.75rem;">Vistas totales = histórico completo del artículo. Conversiones = solo dentro del rango seleccionado arriba.</p>
    @endif
</div>
@endsection
