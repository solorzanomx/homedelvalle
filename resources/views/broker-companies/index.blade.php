@extends('layouts.app-sidebar')
@section('title', 'Empresas')

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
.avatar-cell img { width: 36px; height: 36px; border-radius: 8px; object-fit: cover; }
.avatar-cell .av-sm {
    width: 36px; height: 36px; border-radius: 8px;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark, #764ba2));
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-weight: 600; font-size: 0.8rem;
}
</style>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h2>Empresas</h2>
        <p class="text-muted">Inmobiliarias y empresas asociadas</p>
    </div>
    <a href="{{ route('broker-companies.create') }}" class="btn btn-primary">+ Nueva Empresa</a>
</div>

{{-- Stats --}}
<div class="stat-cards" style="display:flex; flex-direction:row; flex-wrap:nowrap;">
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(102,126,234,0.1); color:var(--primary);">&#127970;</div>
        <div><div class="stat-value">{{ $stats['total'] }}</div><div class="stat-label">Total Empresas</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(34,197,94,0.1); color:var(--success);">&#10003;</div>
        <div><div class="stat-value">{{ $stats['active'] }}</div><div class="stat-label">Activas</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(168,85,247,0.1); color:#a855f7;">&#9734;</div>
        <div><div class="stat-value">{{ $stats['brokers'] }}</div><div class="stat-label">Brokers Vinculados</div></div>
    </div>
</div>

{{-- Filtros --}}
<form method="GET" action="{{ route('broker-companies.index') }}" class="filter-bar">
    <div class="filter-grid">
        <div class="form-group" style="margin:0;">
            <label class="form-label">Buscar</label>
            <input type="text" name="search" class="form-input" value="{{ request('search') }}" placeholder="Nombre, contacto, ciudad...">
        </div>
        <div class="form-group" style="margin:0;">
            <label class="form-label">Estado</label>
            <select name="status" class="form-select">
                <option value="">Todos</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Activa</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactiva</option>
            </select>
        </div>
    </div>
    <div class="filter-actions">
        <button type="submit" class="btn btn-primary btn-sm">Buscar</button>
        <a href="{{ route('broker-companies.index') }}" class="btn btn-outline btn-sm">Limpiar</a>
    </div>
</form>

{{-- Tabla --}}
<div class="card">
    <div class="card-body" style="padding:0;">
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th></th>
                        <th>Nombre</th>
                        <th>Contacto</th>
                        <th>Telefono</th>
                        <th>Ciudad</th>
                        <th>Brokers</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($companies as $company)
                    <tr>
                        <td class="avatar-cell">
                            @if($company->logo)
                                <img src="{{ asset('storage/' . $company->logo) }}" alt="">
                            @else
                                <div class="av-sm">{{ strtoupper(substr($company->name, 0, 1)) }}</div>
                            @endif
                        </td>
                        <td style="font-weight:500;">{{ $company->name }}</td>
                        <td class="text-muted" style="font-size:0.85rem;">{{ $company->contact_name ?: '—' }}</td>
                        <td class="text-muted" style="font-size:0.85rem;">{{ $company->phone ?: '—' }}</td>
                        <td class="text-muted" style="font-size:0.85rem;">{{ $company->city ?: '—' }}</td>
                        <td style="font-size:0.85rem; text-align:center;">{{ $company->brokers_count }}</td>
                        <td>
                            @if($company->status === 'active')
                                <span class="badge badge-green">Activa</span>
                            @else
                                <span class="badge badge-red">Inactiva</span>
                            @endif
                        </td>
                        <td>
                            <div class="action-btns">
                                <a href="{{ route('broker-companies.edit', $company) }}" class="btn btn-sm btn-outline">Editar</a>
                                <form method="POST" action="{{ route('broker-companies.destroy', $company) }}" style="display:inline" onsubmit="return confirm('Eliminar esta empresa? Los brokers vinculados no seran eliminados.')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted" style="padding:2rem;">No hay empresas registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($companies->hasPages())
        <div style="padding:1rem 1.5rem; border-top:1px solid var(--border);">{{ $companies->links() }}</div>
        @endif
    </div>
</div>
@endsection
