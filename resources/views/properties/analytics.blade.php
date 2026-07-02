@extends('layouts.app-sidebar')
@section('title', isset($property) ? 'Analítica · ' . $property->title : 'Analítica de Vistas')

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
.an-rank { display: inline-flex; align-items: center; justify-content: center; width: 22px; height: 22px; border-radius: 50%; background: var(--bg,#f1f5f9); font-size: 0.72rem; font-weight: 700; color: var(--text-muted); }
</style>
@endsection

@section('content')
<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;margin-bottom:1.25rem;">
    <div>
        <h2 style="margin:0;font-size:1.3rem;">
            @if(isset($property))
                &#128200; {{ $property->title }}
            @else
                &#128200; Analítica de Vistas
            @endif
        </h2>
        <p style="margin:0.2rem 0 0;color:var(--text-muted);font-size:0.85rem;">
            @if(isset($property))
                Vistas de la ficha pública de esta propiedad · <a href="{{ route('properties.show', $property) }}">Volver a la ficha →</a>
            @else
                Vistas de todas las propiedades en el sitio público
            @endif
        </p>
    </div>
    <div class="an-range">
        @foreach([7 => '7 días', 30 => '30 días', 90 => '90 días'] as $d => $label)
        <a href="{{ route('properties.analytics', array_filter(['range' => $d, 'property' => $property->id ?? null])) }}"
           class="{{ $rangeDays === $d ? 'active' : '' }}">{{ $label }}</a>
        @endforeach
    </div>
</div>

@if(isset($property))
{{-- ═══════════ DRILL-DOWN: una sola propiedad ═══════════ --}}
<div class="an-stats">
    <div class="an-stat">
        <div class="an-stat-val">{{ number_format($propertyViewsTotal) }}</div>
        <div class="an-stat-label">Vistas totales ({{ $rangeDays }} días)</div>
    </div>
    <div class="an-stat">
        <div class="an-stat-val">{{ number_format($propertyViewsUnique) }}</div>
        <div class="an-stat-label">Visitantes únicos</div>
    </div>
</div>

<div class="an-card">
    <h3>Tendencia diaria</h3>
    @if($propertyViewsTotal > 0)
    <div style="position:relative;width:100%;height:220px;">
        <canvas id="analyticsChart"></canvas>
    </div>
    @else
    <p style="color:var(--text-muted);font-size:0.85rem;">Sin vistas registradas en este rango.</p>
    @endif
</div>

<div class="an-card">
    <h3>Vistas recientes</h3>
    @if($recentViews->isEmpty())
    <p style="color:var(--text-muted);font-size:0.85rem;">Sin vistas registradas en este rango.</p>
    @else
    <table class="an-table">
        <thead><tr><th>Fecha</th><th>Origen</th><th>IP</th></tr></thead>
        <tbody>
            @foreach($recentViews as $view)
            <tr>
                <td>{{ $view->viewed_at->format('d/m/Y H:i') }}</td>
                <td style="max-width:280px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $view->referrer ?: '— Directo —' }}</td>
                <td>{{ $view->ip_address ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>

<script>
const chartData = @json($trendData);
</script>

@else
{{-- ═══════════ RANKING GENERAL ═══════════ --}}
<div class="an-stats">
    <div class="an-stat">
        <div class="an-stat-val">{{ number_format($viewsTotal) }}</div>
        <div class="an-stat-label">Vistas totales ({{ $rangeDays }} días)</div>
    </div>
    <div class="an-stat">
        <div class="an-stat-val">{{ number_format($viewsUnique) }}</div>
        <div class="an-stat-label">Visitantes únicos</div>
    </div>
    <div class="an-stat">
        <div class="an-stat-val" style="font-size:1.1rem;">{{ $topProperty->title ?? '—' }}</div>
        <div class="an-stat-label">Propiedad más vista ({{ number_format($topProperty->views_count ?? 0) }} vistas)</div>
    </div>
    <div class="an-stat">
        <div class="an-stat-val">{{ $avgPerProperty }}</div>
        <div class="an-stat-label">Promedio de vistas por propiedad</div>
    </div>
</div>

<div class="an-card">
    <h3>Tendencia diaria</h3>
    @if($viewsTotal > 0)
    <div style="position:relative;width:100%;height:260px;">
        <canvas id="analyticsChart"></canvas>
    </div>
    @else
    <p style="color:var(--text-muted);font-size:0.85rem;">Sin vistas registradas en este rango.</p>
    @endif
</div>

<div class="an-card">
    <h3>Ranking de propiedades más vistas</h3>
    @if($ranking->isEmpty())
    <p style="color:var(--text-muted);font-size:0.85rem;">Sin vistas registradas en este rango todavía.</p>
    @else
    <table class="an-table">
        <thead><tr><th>#</th><th>Propiedad</th><th>Vistas</th><th>Únicas</th><th></th></tr></thead>
        <tbody>
            @foreach($ranking as $i => $p)
            <tr>
                <td><span class="an-rank">{{ $i + 1 }}</span></td>
                <td>
                    <div style="font-weight:600;">{{ $p->title }}</div>
                    <div style="font-size:0.75rem;color:var(--text-muted);">{{ $p->colony }}, {{ $p->city }}</div>
                </td>
                <td style="font-weight:700;">{{ number_format($p->views_count) }}</td>
                <td>{{ number_format($p->unique_count) }}</td>
                <td><a href="{{ route('properties.analytics', ['property' => $p->id, 'range' => $rangeDays]) }}" style="font-size:0.78rem;">Ver detalle →</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>

<script>
const chartData = @json($trendData);
</script>
@endif

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function() {
    const el = document.getElementById('analyticsChart');
    if (!el || typeof chartData === 'undefined') return;
    const ctx = el.getContext('2d');
    const color = '#2563eb';
    const gradient = ctx.createLinearGradient(0, 0, 0, 200);
    gradient.addColorStop(0, color + '30');
    gradient.addColorStop(1, color + '00');

    Chart.defaults.font.family = '-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif';
    Chart.defaults.font.size = 11;

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.map(d => d.date.slice(5)),
            datasets: [{
                label: 'Vistas',
                data: chartData.map(d => d.count),
                borderColor: color,
                backgroundColor: gradient,
                fill: true,
                borderWidth: 2.5,
                pointRadius: 0,
                tension: .35,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false } },
                y: { beginAtZero: true, ticks: { precision: 0 } },
            },
        },
    });
})();
</script>
@endsection
