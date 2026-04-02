@extends('layouts.app-sidebar')
@section('title', 'Finanzas')

@section('styles')
<style>
/* Nav pills */
.fin-pills { display: flex; gap: 0.4rem; margin-bottom: 1.25rem; overflow-x: auto; padding-bottom: 2px; }
.fin-pill {
    padding: 0.45rem 0.9rem; border-radius: 20px; font-size: 0.78rem; font-weight: 500;
    border: 1px solid var(--border); background: var(--card); color: var(--text-muted);
    text-decoration: none; white-space: nowrap; transition: all 0.15s;
}
.fin-pill:hover { border-color: var(--primary); color: var(--text); }
.fin-pill.active { background: var(--primary); color: #fff; border-color: var(--primary); }

/* Chart */
.chart-card { background: var(--card); border: 1px solid var(--border); border-radius: 10px; overflow: hidden; margin-bottom: 1.25rem; }
.chart-card-header { padding: 0.75rem 1.25rem; border-bottom: 1px solid var(--border); font-weight: 600; font-size: 0.85rem; }
.chart-card-body { padding: 1.25rem; }
.chart-bar-row { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem; }
.chart-bar-row:last-child { margin-bottom: 0; }
.chart-label { width: 65px; font-size: 0.75rem; color: var(--text-muted); text-align: right; flex-shrink: 0; }
.chart-bars { flex: 1; display: flex; flex-direction: column; gap: 3px; }
.chart-bar {
    height: 20px; border-radius: 4px; min-width: 3px; display: flex; align-items: center;
    padding-left: 8px; font-size: 0.7rem; color: #fff; font-weight: 500; transition: width 0.4s ease;
}
.chart-bar.income { background: linear-gradient(90deg, #10b981, #34d399); }
.chart-bar.expense { background: linear-gradient(90deg, #ef4444, #f87171); }
.chart-legend { display: flex; gap: 1.5rem; margin-top: 1rem; padding-top: 0.75rem; border-top: 1px solid var(--border); }
.chart-legend-item { display: flex; align-items: center; gap: 0.4rem; font-size: 0.78rem; color: var(--text-muted); }
.chart-legend-dot { width: 10px; height: 10px; border-radius: 3px; }

/* Two columns */
.fin-columns { display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; }
.fin-card { background: var(--card); border: 1px solid var(--border); border-radius: 10px; overflow: hidden; }
.fin-card-header {
    padding: 0.75rem 1.25rem; border-bottom: 1px solid var(--border);
    display: flex; justify-content: space-between; align-items: center;
    font-weight: 600; font-size: 0.85rem;
}

/* Transaction item */
.tx-item { display: flex; align-items: center; gap: 0.75rem; padding: 0.65rem 1.25rem; border-bottom: 1px solid var(--border); }
.tx-item:last-child { border-bottom: none; }
.tx-icon { width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 0.85rem; flex-shrink: 0; }
.tx-icon.income { background: #dcfce7; color: #166534; }
.tx-icon.expense { background: #fef2f2; color: #991b1b; }
.tx-info { flex: 1; min-width: 0; }
.tx-desc { font-size: 0.85rem; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.tx-date { font-size: 0.72rem; color: var(--text-muted); }
.tx-amount { font-weight: 600; font-size: 0.88rem; flex-shrink: 0; }
.tx-amount.income-amt { color: #065f46; }
.tx-amount.expense-amt { color: #991b1b; }

/* Commission item */
.comm-item { display: flex; align-items: center; gap: 0.75rem; padding: 0.65rem 1.25rem; border-bottom: 1px solid var(--border); }
.comm-item:last-child { border-bottom: none; }
.comm-avatar { width: 32px; height: 32px; border-radius: 50%; background: var(--primary); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 600; flex-shrink: 0; }
.comm-info { flex: 1; min-width: 0; }
.comm-broker { font-size: 0.85rem; font-weight: 500; }
.comm-deal { font-size: 0.72rem; color: var(--text-muted); }
.comm-right { display: flex; align-items: center; gap: 0.5rem; flex-shrink: 0; }
.comm-amount { font-weight: 600; font-size: 0.88rem; }

/* Empty */
.fin-empty { text-align: center; padding: 2rem; color: var(--text-muted); font-size: 0.85rem; }

@media (max-width: 1024px) { .fin-columns { grid-template-columns: 1fr; } }
</style>
@endsection

@section('content')
{{-- Stats --}}
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon bg-green">&#9650;</div>
        <div>
            <div class="stat-value">${{ number_format($stats['income_month'], 0) }}</div>
            <div class="stat-label">Ingresos del mes</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#ef4444;">&#9660;</div>
        <div>
            <div class="stat-value">${{ number_format($stats['expense_month'], 0) }}</div>
            <div class="stat-label">Egresos del mes</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon bg-orange">&#9203;</div>
        <div>
            <div class="stat-value">${{ number_format($stats['pending_commissions'], 0) }}</div>
            <div class="stat-label">Comisiones pendientes</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon bg-blue">&#10003;</div>
        <div>
            <div class="stat-value">${{ number_format($stats['total_commissions_paid'], 0) }}</div>
            <div class="stat-label">Comisiones pagadas</div>
        </div>
    </div>
</div>

{{-- Header --}}
<div class="page-header">
    <h2>Panel Financiero</h2>
    <div style="display:flex; gap:0.5rem;">
        <a href="{{ route('admin.finance.transactions') }}" class="btn btn-outline">Ver Transacciones</a>
        <a href="{{ route('admin.finance.transactions.create') }}" class="btn btn-primary">+ Nueva</a>
    </div>
</div>

{{-- Nav pills --}}
<div class="fin-pills">
    <a href="{{ route('admin.finance.dashboard') }}" class="fin-pill active">Resumen</a>
    <a href="{{ route('admin.finance.transactions') }}" class="fin-pill">Transacciones</a>
    <a href="{{ route('admin.finance.commissions') }}" class="fin-pill">Comisiones</a>
</div>

{{-- Chart --}}
<div class="chart-card">
    <div class="chart-card-header">Ingresos vs Egresos (6 meses)</div>
    <div class="chart-card-body">
        @php
            $maxVal = max(1, collect($monthlyData)->flatMap(fn($m) => [$m['income'], $m['expense']])->max());
        @endphp
        @foreach($monthlyData as $month)
            <div class="chart-bar-row">
                <div class="chart-label">{{ $month['label'] }}</div>
                <div class="chart-bars">
                    <div class="chart-bar income" style="width: {{ ($month['income'] / $maxVal) * 100 }}%;">
                        @if($month['income'] > 0) ${{ number_format($month['income'], 0) }} @endif
                    </div>
                    <div class="chart-bar expense" style="width: {{ ($month['expense'] / $maxVal) * 100 }}%;">
                        @if($month['expense'] > 0) ${{ number_format($month['expense'], 0) }} @endif
                    </div>
                </div>
            </div>
        @endforeach
        <div class="chart-legend">
            <div class="chart-legend-item"><div class="chart-legend-dot" style="background:#10b981;"></div> Ingresos</div>
            <div class="chart-legend-item"><div class="chart-legend-dot" style="background:#ef4444;"></div> Egresos</div>
        </div>
    </div>
</div>

{{-- Two columns --}}
<div class="fin-columns">
    {{-- Recent Transactions --}}
    <div class="fin-card">
        <div class="fin-card-header">
            <span>Transacciones Recientes</span>
            <a href="{{ route('admin.finance.transactions') }}" class="btn btn-sm btn-outline">Ver todas</a>
        </div>
        @forelse($recentTransactions as $tx)
            <div class="tx-item">
                <div class="tx-icon {{ $tx->type }}">{{ $tx->type === 'income' ? '&#9650;' : '&#9660;' }}</div>
                <div class="tx-info">
                    <div class="tx-desc">{{ Str::limit($tx->description, 30) }}</div>
                    <div class="tx-date">{{ \Carbon\Carbon::parse($tx->date)->format('d/m/Y') }}</div>
                </div>
                <div class="tx-amount {{ $tx->type === 'income' ? 'income-amt' : 'expense-amt' }}">
                    {{ $tx->type === 'income' ? '+' : '-' }}${{ number_format($tx->amount, 0) }}
                </div>
            </div>
        @empty
            <div class="fin-empty">No hay transacciones registradas</div>
        @endforelse
    </div>

    {{-- Pending Commissions --}}
    <div class="fin-card">
        <div class="fin-card-header">
            <span>Comisiones Pendientes</span>
            <a href="{{ route('admin.finance.commissions') }}" class="btn btn-sm btn-outline">Ver todas</a>
        </div>
        @forelse($pendingCommissions as $comm)
            <div class="comm-item">
                <div class="comm-avatar">{{ strtoupper(substr($comm->broker->name ?? 'N', 0, 1)) }}</div>
                <div class="comm-info">
                    <div class="comm-broker">{{ $comm->broker->name ?? 'Sin broker' }}</div>
                    <div class="comm-deal">{{ $comm->deal->property->title ?? 'Deal #' . $comm->deal_id }}</div>
                </div>
                <div class="comm-right">
                    <div class="comm-amount">${{ number_format($comm->amount, 0) }}</div>
                    <form action="{{ route('admin.finance.commissions.approve', $comm->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-primary">Aprobar</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="fin-empty">No hay comisiones pendientes</div>
        @endforelse
    </div>
</div>
@endsection
