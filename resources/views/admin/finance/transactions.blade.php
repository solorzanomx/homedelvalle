@extends('layouts.app-sidebar')
@section('title', 'Transacciones')

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

/* Filter bar collapsible */
.filter-toggle {
    display: flex; align-items: center; gap: 0.4rem; padding: 0.5rem 0.75rem;
    background: var(--card); border: 1px solid var(--border); border-radius: var(--radius);
    font-size: 0.82rem; font-weight: 500; cursor: pointer; margin-bottom: 0.75rem;
    color: var(--text-muted); transition: all 0.15s; width: fit-content;
}
.filter-toggle:hover { border-color: var(--primary); color: var(--text); }
.filter-toggle .chevron { transition: transform 0.2s; font-size: 0.7rem; }
.filter-toggle.open .chevron { transform: rotate(180deg); }
.filter-body {
    display: none; flex-wrap: wrap; gap: 0.75rem; align-items: flex-end;
    padding: 1rem 1.25rem; background: var(--card); border: 1px solid var(--border); border-radius: var(--radius);
    margin-bottom: 1.25rem;
}
.filter-body.open { display: flex; }
.filter-group { display: flex; flex-direction: column; gap: 0.25rem; }
.filter-group label { font-size: 0.72rem; font-weight: 500; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; }
.filter-group select, .filter-group input {
    padding: 0.45rem 0.7rem; font-size: 0.85rem; border: 1px solid var(--border);
    border-radius: var(--radius); background: var(--bg); font-family: inherit;
}

/* Type pills */
.type-pills { display: flex; gap: 0.5rem; margin-bottom: 1.25rem; }
.type-pill {
    display: flex; align-items: center; gap: 0.4rem; padding: 0.45rem 0.9rem; border-radius: 20px;
    font-size: 0.78rem; font-weight: 500; border: 1px solid var(--border); background: var(--card);
    color: var(--text-muted); text-decoration: none; white-space: nowrap; transition: all 0.15s;
}
.type-pill:hover { border-color: var(--primary); color: var(--text); }
.type-pill.active { background: var(--primary); color: #fff; border-color: var(--primary); }
.type-pill.active-income { background: #10b981; color: #fff; border-color: #10b981; }
.type-pill.active-expense { background: #ef4444; color: #fff; border-color: #ef4444; }

/* Transaction list */
.tx-list { background: var(--card); border: 1px solid var(--border); border-radius: 10px; overflow: hidden; }
.tx-list-header {
    padding: 0.75rem 1.25rem; border-bottom: 1px solid var(--border);
    font-weight: 600; font-size: 0.85rem; display: flex; justify-content: space-between; align-items: center;
}
.tx-item {
    display: flex; align-items: center; gap: 0.75rem; padding: 0.7rem 1.25rem;
    border-bottom: 1px solid var(--border); transition: background 0.1s;
}
.tx-item:last-child { border-bottom: none; }
.tx-item:hover { background: rgba(248,250,252,0.8); }
.tx-icon { width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 0.88rem; flex-shrink: 0; }
.tx-icon.income { background: #dcfce7; color: #166534; }
.tx-icon.expense { background: #fef2f2; color: #991b1b; }
.tx-info { flex: 1; min-width: 0; }
.tx-desc { font-size: 0.88rem; font-weight: 500; }
.tx-sub { font-size: 0.75rem; color: var(--text-muted); display: flex; gap: 0.5rem; flex-wrap: wrap; margin-top: 0.1rem; }
.tx-right { display: flex; align-items: center; gap: 0.75rem; flex-shrink: 0; }
.tx-amount { font-weight: 600; font-size: 0.92rem; }
.tx-amount.income-amt { color: #065f46; }
.tx-amount.expense-amt { color: #991b1b; }
.tx-actions { display: flex; gap: 0.3rem; }

/* Empty */
.tx-empty { text-align: center; padding: 3rem; color: var(--text-muted); font-size: 0.88rem; }

@media (max-width: 768px) {
    .tx-sub { display: none; }
    .tx-actions { flex-direction: column; }
}
</style>
@endsection

@section('content')
@php
    $methods = ['cash' => 'Efectivo', 'transfer' => 'Transferencia', 'check' => 'Cheque', 'card' => 'Tarjeta', 'other' => 'Otro'];
@endphp

<div class="page-header">
    <div>
        <h2>Transacciones</h2>
        <p class="text-muted">{{ $transactions->total() }} transaccion{{ $transactions->total() !== 1 ? 'es' : '' }}</p>
    </div>
    <a href="{{ route('admin.finance.transactions.create') }}" class="btn btn-primary" style="white-space:nowrap;">+ Nueva</a>
</div>

{{-- Nav pills --}}
<div class="fin-pills">
    <a href="{{ route('admin.finance.dashboard') }}" class="fin-pill">Resumen</a>
    <a href="{{ route('admin.finance.transactions') }}" class="fin-pill active">Transacciones</a>
    <a href="{{ route('admin.finance.commissions') }}" class="fin-pill">Comisiones</a>
</div>

{{-- Type pills --}}
<div class="type-pills">
    <a href="{{ route('admin.finance.transactions', request()->except('type')) }}" class="type-pill {{ !request('type') ? 'active' : '' }}">Todas</a>
    <a href="{{ route('admin.finance.transactions', array_merge(request()->query(), ['type' => 'income'])) }}" class="type-pill {{ request('type') === 'income' ? 'active-income' : '' }}" style="{{ request('type') !== 'income' ? 'border-color:#86efac; color:#10b981;' : '' }}">&#9650; Ingresos</a>
    <a href="{{ route('admin.finance.transactions', array_merge(request()->query(), ['type' => 'expense'])) }}" class="type-pill {{ request('type') === 'expense' ? 'active-expense' : '' }}" style="{{ request('type') !== 'expense' ? 'border-color:#fca5a5; color:#ef4444;' : '' }}">&#9660; Egresos</a>
</div>

{{-- Collapsible filter --}}
<div class="filter-toggle" onclick="this.classList.toggle('open'); document.getElementById('filterBody').classList.toggle('open');">
    &#9881; Filtros <span class="chevron">&#9660;</span>
    @if(request()->hasAny(['category', 'from', 'to']))
        <span class="badge badge-blue" style="font-size:0.65rem;">Activos</span>
    @endif
</div>
<form action="{{ route('admin.finance.transactions') }}" method="GET" id="filterBody" class="filter-body {{ request()->hasAny(['category', 'from', 'to']) ? 'open' : '' }}">
    @if(request('type'))<input type="hidden" name="type" value="{{ request('type') }}">@endif
    <div class="filter-group">
        <label>Categoria</label>
        <select name="category">
            <option value="">Todas</option>
            @foreach($categories as $cat)
                <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ ucfirst($cat) }}</option>
            @endforeach
        </select>
    </div>
    <div class="filter-group">
        <label>Desde</label>
        <input type="date" name="from" value="{{ request('from') }}">
    </div>
    <div class="filter-group">
        <label>Hasta</label>
        <input type="date" name="to" value="{{ request('to') }}">
    </div>
    <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
    @if(request()->hasAny(['type', 'category', 'from', 'to']))
        <a href="{{ route('admin.finance.transactions') }}" class="btn btn-outline btn-sm">Limpiar</a>
    @endif
</form>

{{-- Transaction List --}}
<div class="tx-list">
    @forelse($transactions as $tx)
        <div class="tx-item">
            <div class="tx-icon {{ $tx->type }}">{{ $tx->type === 'income' ? '&#9650;' : '&#9660;' }}</div>
            <div class="tx-info">
                <div class="tx-desc">{{ $tx->description }}</div>
                <div class="tx-sub">
                    <span>{{ \Carbon\Carbon::parse($tx->date)->format('d/m/Y') }}</span>
                    <span>&middot; {{ ucfirst($tx->category) }}</span>
                    <span>&middot; {{ $methods[$tx->payment_method] ?? $tx->payment_method }}</span>
                    @if($tx->property)<span>&middot; {{ Str::limit($tx->property->title, 20) }}</span>@endif
                    @if($tx->broker)<span>&middot; {{ $tx->broker->name }}</span>@endif
                </div>
            </div>
            <div class="tx-right">
                <div class="tx-amount {{ $tx->type === 'income' ? 'income-amt' : 'expense-amt' }}">
                    {{ $tx->type === 'income' ? '+' : '-' }}${{ number_format($tx->amount, 2) }}
                    <span style="font-size:0.68rem; font-weight:400; color:var(--text-muted);">{{ $tx->currency }}</span>
                </div>
                <div class="tx-actions">
                    <a href="{{ route('admin.finance.transactions.edit', $tx->id) }}" class="btn btn-sm btn-outline" style="padding:0.2rem 0.5rem; font-size:0.72rem;">&#9998;</a>
                    <form action="{{ route('admin.finance.transactions.destroy', $tx->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Eliminar esta transaccion?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" style="padding:0.2rem 0.5rem; font-size:0.72rem;">&#10005;</button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="tx-empty">
            No se encontraron transacciones.<br>
            <a href="{{ route('admin.finance.transactions.create') }}" style="color:var(--primary); font-weight:500;">+ Registrar primera transaccion</a>
        </div>
    @endforelse
</div>

@if($transactions->hasPages())
<div style="margin-top:1rem; text-align:center;">{{ $transactions->links() }}</div>
@endif
@endsection
