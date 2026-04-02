@extends('layouts.app-sidebar')
@section('title', 'Comisiones')

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

/* Status pills */
.status-pills { display: flex; gap: 0.5rem; margin-bottom: 1.25rem; }
.status-pill {
    display: flex; align-items: center; gap: 0.4rem; padding: 0.45rem 0.9rem; border-radius: 20px;
    font-size: 0.78rem; font-weight: 500; border: 1px solid var(--border); background: var(--card);
    color: var(--text-muted); text-decoration: none; white-space: nowrap; transition: all 0.15s;
}
.status-pill:hover { border-color: var(--primary); color: var(--text); }
.status-pill.active { background: var(--primary); color: #fff; border-color: var(--primary); }

/* Filter */
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
.filter-group select { padding: 0.45rem 0.7rem; font-size: 0.85rem; border: 1px solid var(--border); border-radius: var(--radius); background: var(--bg); font-family: inherit; }

/* Commission list */
.comm-list { background: var(--card); border: 1px solid var(--border); border-radius: 10px; overflow: hidden; }
.comm-item {
    display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1.25rem;
    border-bottom: 1px solid var(--border); transition: background 0.1s;
}
.comm-item:last-child { border-bottom: none; }
.comm-item:hover { background: rgba(248,250,252,0.8); }
.comm-avatar {
    width: 40px; height: 40px; border-radius: 50%; background: var(--primary); color: #fff;
    display: flex; align-items: center; justify-content: center; font-size: 0.82rem; font-weight: 600;
    flex-shrink: 0;
}
.comm-info { flex: 1; min-width: 0; }
.comm-broker { font-size: 0.88rem; font-weight: 500; }
.comm-detail { font-size: 0.75rem; color: var(--text-muted); display: flex; gap: 0.5rem; flex-wrap: wrap; }
.comm-right { display: flex; align-items: center; gap: 0.75rem; flex-shrink: 0; }
.comm-amount { font-weight: 600; font-size: 0.95rem; }
.comm-pct { font-size: 0.72rem; color: var(--text-muted); }

/* Empty */
.comm-empty { text-align: center; padding: 3rem; color: var(--text-muted); font-size: 0.88rem; }

/* Progress bar */
.comm-progress {
    display: flex; gap: 0.25rem; margin-bottom: 1.25rem;
}
.comm-progress-item {
    flex: 1; height: 4px; border-radius: 2px; background: var(--border);
}
.comm-progress-item.filled-yellow { background: #f59e0b; }
.comm-progress-item.filled-blue { background: #3b82f6; }
.comm-progress-item.filled-green { background: #10b981; }
</style>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h2>Comisiones</h2>
        <p class="text-muted">{{ $commissions->total() }} comision{{ $commissions->total() !== 1 ? 'es' : '' }}</p>
    </div>
    <a href="{{ route('admin.finance.dashboard') }}" class="btn btn-outline">&#8592; Finanzas</a>
</div>

{{-- Nav pills --}}
<div class="fin-pills">
    <a href="{{ route('admin.finance.dashboard') }}" class="fin-pill">Resumen</a>
    <a href="{{ route('admin.finance.transactions') }}" class="fin-pill">Transacciones</a>
    <a href="{{ route('admin.finance.commissions') }}" class="fin-pill active">Comisiones</a>
</div>

{{-- Status pills --}}
<div class="status-pills">
    <a href="{{ route('admin.finance.commissions') }}" class="status-pill {{ !request('status') ? 'active' : '' }}">Todas</a>
    <a href="{{ route('admin.finance.commissions', ['status' => 'pending']) }}" class="status-pill {{ request('status') === 'pending' ? 'active' : '' }}" style="{{ request('status') !== 'pending' ? 'border-color:#fde68a; color:#f59e0b;' : '' }}">Pendientes</a>
    <a href="{{ route('admin.finance.commissions', ['status' => 'approved']) }}" class="status-pill {{ request('status') === 'approved' ? 'active' : '' }}" style="{{ request('status') !== 'approved' ? 'border-color:#93c5fd; color:#3b82f6;' : '' }}">Aprobadas</a>
    <a href="{{ route('admin.finance.commissions', ['status' => 'paid']) }}" class="status-pill {{ request('status') === 'paid' ? 'active' : '' }}" style="{{ request('status') !== 'paid' ? 'border-color:#86efac; color:#10b981;' : '' }}">Pagadas</a>
</div>

{{-- Broker filter --}}
@if(request('broker_id'))
<div style="margin-bottom:1rem;">
    <span class="badge badge-blue" style="font-size:0.78rem;">
        Broker: {{ $brokers->firstWhere('id', request('broker_id'))->name ?? '—' }}
        <a href="{{ route('admin.finance.commissions', request()->except('broker_id')) }}" style="color:inherit; margin-left:0.3rem;">&#10005;</a>
    </span>
</div>
@else
<div class="filter-toggle" onclick="this.classList.toggle('open'); document.getElementById('filterBody').classList.toggle('open');">
    &#9881; Filtrar por broker <span class="chevron">&#9660;</span>
</div>
<form action="{{ route('admin.finance.commissions') }}" method="GET" id="filterBody" class="filter-body">
    @if(request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif
    <div class="filter-group">
        <label>Broker</label>
        <select name="broker_id">
            <option value="">Todos</option>
            @foreach($brokers as $broker)
                <option value="{{ $broker->id }}">{{ $broker->name }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
</form>
@endif

{{-- Commission List --}}
<div class="comm-list">
    @forelse($commissions as $comm)
        <div class="comm-item">
            <div class="comm-avatar">{{ strtoupper(substr($comm->broker->name ?? 'N', 0, 1)) }}</div>
            <div class="comm-info">
                <div class="comm-broker">{{ $comm->broker->name ?? 'N/A' }}</div>
                <div class="comm-detail">
                    @if($comm->deal)
                        <span>Deal #{{ $comm->deal->id }}</span>
                        @if($comm->deal->property)<span>&middot; {{ Str::limit($comm->deal->property->title, 25) }}</span>@endif
                        @if($comm->deal->client)<span>&middot; {{ $comm->deal->client->name }}</span>@endif
                    @endif
                    @if($comm->percentage)<span>&middot; {{ number_format($comm->percentage, 1) }}%</span>@endif
                    @if($comm->paid_at)<span>&middot; Pagada {{ \Carbon\Carbon::parse($comm->paid_at)->format('d/m/Y') }}</span>@endif
                </div>
                {{-- Mini progress bar --}}
                <div style="display:flex; gap:3px; margin-top:0.35rem; width:80px;">
                    <div style="flex:1; height:3px; border-radius:2px; background:{{ in_array($comm->status, ['pending','approved','paid']) ? '#f59e0b' : 'var(--border)' }};"></div>
                    <div style="flex:1; height:3px; border-radius:2px; background:{{ in_array($comm->status, ['approved','paid']) ? '#3b82f6' : 'var(--border)' }};"></div>
                    <div style="flex:1; height:3px; border-radius:2px; background:{{ $comm->status === 'paid' ? '#10b981' : 'var(--border)' }};"></div>
                </div>
            </div>
            <div class="comm-right">
                <div>
                    <div class="comm-amount">${{ number_format($comm->amount, 2) }}</div>
                </div>
                @if($comm->status === 'pending')
                    <form action="{{ route('admin.finance.commissions.approve', $comm->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-primary" style="font-size:0.75rem;">Aprobar</button>
                    </form>
                @elseif($comm->status === 'approved')
                    <form action="{{ route('admin.finance.commissions.pay', $comm->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-sm" style="background:#10b981; color:#fff; border:none; font-size:0.75rem;">Pagar</button>
                    </form>
                @elseif($comm->status === 'paid')
                    <span class="badge badge-green">Pagada</span>
                @endif
            </div>
        </div>
    @empty
        <div class="comm-empty">
            No se encontraron comisiones{{ request('status') ? ' con ese estado' : '' }}.
        </div>
    @endforelse
</div>

@if($commissions->hasPages())
<div style="margin-top:1rem; text-align:center;">{{ $commissions->links() }}</div>
@endif
@endsection
