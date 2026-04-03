@extends('layouts.app-sidebar')
@section('title', 'Brokers')

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
.view-toggle {
    display: flex; gap: 0.25rem; background: var(--bg); border-radius: var(--radius); padding: 3px;
}
.view-toggle .btn { justify-content: center; min-width: 38px; }
.broker-cards {
    display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.25rem;
}
@media (max-width: 1024px) { .broker-cards { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 640px) { .broker-cards { grid-template-columns: 1fr; } }
.broker-card {
    background: var(--card); border: 1px solid var(--border); border-radius: var(--radius);
    overflow: hidden; transition: box-shadow 0.2s;
}
.broker-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.08); }
.broker-card-header {
    display: flex; align-items: center; gap: 0.75rem; padding: 1rem;
}
.broker-avatar { width: 48px; height: 48px; border-radius: 50%; object-fit: cover; flex-shrink: 0; }
.broker-avatar-ph {
    width: 48px; height: 48px; border-radius: 50%; flex-shrink: 0;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark, #764ba2));
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-weight: 600; font-size: 1.1rem;
}
.broker-card-name { font-weight: 600; font-size: 0.92rem; color: var(--text); }
.broker-card-sub { font-size: 0.78rem; color: var(--text-muted); }
.broker-card-body { padding: 0 1rem 1rem; font-size: 0.82rem; color: var(--text-muted); }
.broker-card-body .meta-row { display: flex; justify-content: space-between; margin-bottom: 0.35rem; }
.broker-card-footer {
    display: flex; gap: 0.5rem; padding: 0.75rem 1rem; border-top: 1px solid var(--border);
}
.avatar-cell img { width: 36px; height: 36px; border-radius: 50%; object-fit: cover; }
.avatar-cell .av-sm {
    width: 36px; height: 36px; border-radius: 50%;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark, #764ba2));
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-weight: 600; font-size: 0.8rem;
}
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
@media (max-width: 640px) { .filter-bar .filter-grid { grid-template-columns: 1fr; } }
</style>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h2>Brokers</h2>
        <p class="text-muted">{{ $brokers->total() }} broker{{ $brokers->total() !== 1 ? 's' : '' }}</p>
    </div>
    <div style="display:flex; gap:0.75rem; align-items:center;">
        <div class="view-toggle">
            <button type="button" class="btn btn-sm" id="btnList" onclick="setView('list')" title="Lista">&#9776;</button>
            <button type="button" class="btn btn-sm" id="btnCards" onclick="setView('cards')" title="Tarjetas">&#9638;</button>
        </div>
        <a href="{{ route('brokers.create') }}" class="btn btn-primary">+ Nuevo Broker</a>
    </div>
</div>

{{-- Stats --}}
<div class="stat-cards" style="display:flex; flex-direction:row; flex-wrap:nowrap;">
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(102,126,234,0.1); color:var(--primary);">&#9734;</div>
        <div><div class="stat-value">{{ $stats['total'] }}</div><div class="stat-label">Total Brokers</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(34,197,94,0.1); color:var(--success);">&#10003;</div>
        <div><div class="stat-value">{{ $stats['active'] }}</div><div class="stat-label">Activos</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(168,85,247,0.1); color:#a855f7;">&#128188;</div>
        <div><div class="stat-value">{{ $stats['operations'] }}</div><div class="stat-label">Operaciones</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(234,179,8,0.1); color:#ca8a04;">&#128176;</div>
        <div><div class="stat-value">${{ number_format($stats['commission'], 0) }}</div><div class="stat-label">Comision Pagada</div></div>
    </div>
</div>

{{-- Filtros --}}
<form method="GET" action="{{ route('brokers.index') }}" class="filter-bar">
    <div class="filter-grid">
        <div class="form-group" style="margin:0;">
            <label class="form-label">Buscar</label>
            <input type="text" name="search" class="form-input" value="{{ request('search') }}" placeholder="Nombre, email, empresa...">
        </div>
        <div class="form-group" style="margin:0;">
            <label class="form-label">Empresa</label>
            <select name="company" class="form-select">
                <option value="">Todas</option>
                @foreach($companies as $company)
                    <option value="{{ $company->id }}" {{ request('company') == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
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
        <a href="{{ route('brokers.index') }}" class="btn btn-outline btn-sm">Limpiar</a>
    </div>
</form>

{{-- Vista Lista --}}
<div class="card view-list" id="viewList">
    <div class="card-body" style="padding:0;">
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th></th>
                        <th>Nombre</th>
                        <th>Empresa</th>
                        <th>Telefono</th>
                        <th>Comision</th>
                        <th>Operaciones</th>
                        <th>Clientes</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($brokers as $broker)
                    <tr>
                        <td class="avatar-cell">
                            @if($broker->photo)
                                <img src="{{ asset('storage/' . $broker->photo) }}" alt="">
                            @else
                                <div class="av-sm">{{ strtoupper(substr($broker->name, 0, 1)) }}</div>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('brokers.show', $broker) }}" style="font-weight:500; color:var(--text);">{{ $broker->name }}</a>
                            <div style="font-size:0.72rem; color:var(--text-muted);">{{ $broker->email }}</div>
                        </td>
                        <td style="font-size:0.85rem;">
                            @if($broker->company)
                                <a href="{{ route('broker-companies.edit', $broker->company) }}" style="color:var(--primary);">{{ $broker->company->name }}</a>
                            @elseif($broker->company_name)
                                <span class="text-muted">{{ $broker->company_name }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-muted" style="font-size:0.85rem;">{{ $broker->phone ?: '—' }}</td>
                        <td style="font-size:0.85rem;">
                            {{ $broker->commission_rate ? $broker->commission_rate . '%' : '—' }}
                        </td>
                        <td style="font-size:0.85rem; text-align:center;">{{ $broker->operations_count ?? 0 }}</td>
                        <td style="font-size:0.85rem; text-align:center;">{{ $broker->clients_count ?? 0 }}</td>
                        <td>
                            @if($broker->status === 'active')
                                <span class="badge badge-green">Activo</span>
                            @else
                                <span class="badge badge-red">Inactivo</span>
                            @endif
                        </td>
                        <td>
                            <div class="action-btns">
                                <a href="{{ route('brokers.show', $broker) }}" class="btn btn-sm btn-outline">Ver</a>
                                <a href="{{ route('brokers.edit', $broker) }}" class="btn btn-sm btn-outline">Editar</a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="text-center text-muted" style="padding:2rem;">No hay brokers registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($brokers->hasPages())
        <div style="padding:1rem 1.5rem; border-top:1px solid var(--border);">{{ $brokers->links() }}</div>
        @endif
    </div>
</div>

{{-- Vista Tarjetas --}}
<div class="view-cards" id="viewCards" style="display:none;">
    <div class="broker-cards">
        @forelse($brokers as $broker)
        <div class="broker-card">
            <div class="broker-card-header">
                @if($broker->photo)
                    <img src="{{ asset('storage/' . $broker->photo) }}" class="broker-avatar" alt="">
                @else
                    <div class="broker-avatar-ph">{{ strtoupper(substr($broker->name, 0, 1)) }}</div>
                @endif
                <div>
                    <div class="broker-card-name">{{ $broker->name }}</div>
                    <div class="broker-card-sub">{{ $broker->company?->name ?: $broker->company_name ?: $broker->email }}</div>
                </div>
            </div>
            <div class="broker-card-body">
                @if($broker->phone)
                    <div class="meta-row"><span>Telefono</span><span>{{ $broker->phone }}</span></div>
                @endif
                @if($broker->commission_rate)
                    <div class="meta-row"><span>Comision</span><span>{{ $broker->commission_rate }}%</span></div>
                @endif
                <div class="meta-row"><span>Operaciones</span><span>{{ $broker->operations_count ?? 0 }}</span></div>
                <div class="meta-row"><span>Clientes</span><span>{{ $broker->clients_count ?? 0 }}</span></div>
                <div style="margin-top:0.5rem;">
                    @if($broker->status === 'active')
                        <span class="badge badge-green">Activo</span>
                    @else
                        <span class="badge badge-red">Inactivo</span>
                    @endif
                </div>
            </div>
            <div class="broker-card-footer">
                <a href="{{ route('brokers.show', $broker) }}" class="btn btn-sm btn-outline" style="flex:1; justify-content:center;">Ver</a>
                <a href="{{ route('brokers.edit', $broker) }}" class="btn btn-sm btn-outline" style="flex:1; justify-content:center;">Editar</a>
            </div>
        </div>
        @empty
        <div class="card" style="grid-column:1/-1;">
            <div class="card-body text-center text-muted" style="padding:3rem;">No hay brokers registrados.</div>
        </div>
        @endforelse
    </div>
    @if($brokers->hasPages())
    <div style="margin-top:1.25rem; text-align:center;">{{ $brokers->links() }}</div>
    @endif
</div>
@endsection

@section('scripts')
<script>
function setView(mode) {
    var list = document.getElementById('viewList'), cards = document.getElementById('viewCards');
    var btnL = document.getElementById('btnList'), btnC = document.getElementById('btnCards');
    if (mode === 'cards') {
        list.style.display = 'none'; cards.style.display = '';
        btnL.className = 'btn btn-sm btn-outline'; btnC.className = 'btn btn-sm btn-primary';
    } else {
        list.style.display = ''; cards.style.display = 'none';
        btnL.className = 'btn btn-sm btn-primary'; btnC.className = 'btn btn-sm btn-outline';
    }
    try { localStorage.setItem('brokers_view', mode); } catch(e) {}
}
(function() {
    var s = 'list'; try { s = localStorage.getItem('brokers_view') || 'list'; } catch(e) {}
    setView(s);
})();
</script>
@endsection
