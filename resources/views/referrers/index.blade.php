@extends('layouts.app-sidebar')
@section('title', 'Comisionistas')

@section('styles')
<style>
.stat-cards { display: flex; gap: 1rem; margin-bottom: 1.25rem; flex-wrap: nowrap; }
.stat-card {
    flex: 1; background: var(--card); border: 1px solid var(--border);
    border-radius: var(--radius); padding: 1rem 1.25rem;
    display: flex; align-items: center; gap: 0.75rem;
}
.stat-icon {
    width: 40px; height: 40px; border-radius: 10px; display: flex;
    align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0;
}
.stat-value { font-size: 1.35rem; font-weight: 700; line-height: 1; }
.stat-label { font-size: 0.72rem; color: var(--text-muted); margin-top: 0.15rem; }
.filter-bar {
    background: var(--card); border: 1px solid var(--border); border-radius: var(--radius);
    padding: 1rem 1.25rem; margin-bottom: 1.25rem;
}
.filter-bar .filter-grid {
    display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 0.75rem; align-items: end;
}
.filter-bar .filter-actions {
    display: flex; gap: 0.5rem; align-items: end; margin-top: 0.75rem;
}
.type-portero { background: rgba(59,130,246,0.1); color: #3b82f6; }
.type-vecino { background: rgba(34,197,94,0.1); color: #22c55e; }
.type-broker_hipotecario { background: rgba(168,85,247,0.1); color: #a855f7; }
.type-comisionista { background: rgba(234,179,8,0.1); color: #ca8a04; }
.type-otro { background: rgba(107,114,128,0.1); color: #6b7280; }
</style>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h2>Comisionistas</h2>
        <p class="text-muted">Red de referidos y captadores</p>
    </div>
    <a href="{{ route('referrers.create') }}" class="btn btn-primary">+ Nuevo Comisionista</a>
</div>

{{-- Stats --}}
<div class="stat-cards" style="display:flex; flex-direction:row; flex-wrap:nowrap;">
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(102,126,234,0.1); color:var(--primary);">&#128101;</div>
        <div><div class="stat-value">{{ $stats['total'] }}</div><div class="stat-label">Total</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(34,197,94,0.1); color:var(--success);">&#10003;</div>
        <div><div class="stat-value">{{ $stats['active'] }}</div><div class="stat-label">Activos</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(168,85,247,0.1); color:#a855f7;">&#128279;</div>
        <div><div class="stat-value">{{ $stats['referrals'] }}</div><div class="stat-label">Referidos</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(234,179,8,0.1); color:#ca8a04;">&#128176;</div>
        <div><div class="stat-value">${{ number_format($stats['paid'], 0) }}</div><div class="stat-label">Total Pagado</div></div>
    </div>
</div>

{{-- Filtros --}}
<form method="GET" action="{{ route('referrers.index') }}" class="filter-bar">
    <div class="filter-grid">
        <div class="form-group" style="margin:0;">
            <label class="form-label">Buscar</label>
            <input type="text" name="search" class="form-input" value="{{ request('search') }}" placeholder="Nombre, telefono, email...">
        </div>
        <div class="form-group" style="margin:0;">
            <label class="form-label">Tipo</label>
            <select name="type" class="form-select">
                <option value="">Todos</option>
                @foreach(\App\Models\Referrer::TYPES as $val => $label)
                    <option value="{{ $val }}" {{ request('type') === $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group" style="margin:0;">
            <label class="form-label">Estado</label>
            <select name="status" class="form-select">
                <option value="">Todos</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Activo</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactivo</option>
            </select>
        </div>
    </div>
    <div class="filter-actions">
        <button type="submit" class="btn btn-primary btn-sm">Buscar</button>
        <a href="{{ route('referrers.index') }}" class="btn btn-outline btn-sm">Limpiar</a>
    </div>
</form>

{{-- Tabla --}}
<div class="card">
    <div class="card-body" style="padding:0;">
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Telefono</th>
                        <th>Tipo</th>
                        <th>Referidos</th>
                        <th>Ganado</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($referrers as $referrer)
                    <tr>
                        <td style="font-weight:500;">
                            <a href="{{ route('referrers.show', $referrer) }}" style="color:var(--text);">{{ $referrer->name }}</a>
                        </td>
                        <td class="text-muted" style="font-size:0.85rem;">{{ $referrer->phone ?: '—' }}</td>
                        <td>
                            <span class="badge type-{{ $referrer->type }}" style="font-size:0.7rem; padding:0.15rem 0.5rem; border-radius:4px;">
                                {{ \App\Models\Referrer::TYPES[$referrer->type] ?? $referrer->type }}
                            </span>
                        </td>
                        <td style="font-size:0.85rem; text-align:center;">{{ $referrer->referrals_count }}</td>
                        <td style="font-size:0.85rem; font-weight:500;">${{ number_format($referrer->total_earned, 0) }}</td>
                        <td>
                            @if($referrer->status === 'active')
                                <span class="badge badge-green">Activo</span>
                            @else
                                <span class="badge badge-red">Inactivo</span>
                            @endif
                        </td>
                        <td>
                            <div class="action-btns">
                                <a href="{{ route('referrers.show', $referrer) }}" class="btn btn-sm btn-outline">Ver</a>
                                <a href="{{ route('referrers.edit', $referrer) }}" class="btn btn-sm btn-outline">Editar</a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted" style="padding:2rem;">No hay comisionistas registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($referrers->hasPages())
        <div style="padding:1rem 1.5rem; border-top:1px solid var(--border);">{{ $referrers->links() }}</div>
        @endif
    </div>
</div>
@endsection
