@extends('layouts.app-sidebar')
@section('title', $broker->name)

@section('styles')
<style>
.profile-header {
    background: var(--card); border: 1px solid var(--border); border-radius: var(--radius);
    padding: 1.5rem; margin-bottom: 1.25rem;
}
.profile-top { display: flex; align-items: center; gap: 1.25rem; }
.profile-avatar { width: 72px; height: 72px; border-radius: 50%; flex-shrink: 0; object-fit: cover; }
.profile-avatar-ph {
    width: 72px; height: 72px; border-radius: 50%; flex-shrink: 0;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark, #764ba2));
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-weight: 700; font-size: 1.75rem;
}
.profile-name { font-size: 1.25rem; font-weight: 700; }
.profile-company { font-size: 0.85rem; color: var(--primary); }
.profile-meta { font-size: 0.82rem; color: var(--text-muted); display: flex; gap: 1rem; flex-wrap: wrap; margin-top: 0.35rem; }
.stat-row { display: flex; gap: 1rem; margin-bottom: 1.25rem; }
.stat-mini {
    flex: 1; background: var(--card); border: 1px solid var(--border);
    border-radius: var(--radius); padding: 1rem; text-align: center;
}
.stat-mini-value { font-size: 1.5rem; font-weight: 700; }
.stat-mini-label { font-size: 0.72rem; color: var(--text-muted); margin-top: 0.15rem; }
.tab-pills {
    display: flex; gap: 2px; background: var(--bg); border-radius: 8px; padding: 3px;
    border: 1px solid var(--border); overflow-x: auto; margin-bottom: 1.25rem;
}
.tab-pill {
    padding: 0.4rem 0.85rem; border-radius: 6px; font-size: 0.78rem; font-weight: 500;
    border: none; background: transparent; color: var(--text-muted);
    cursor: pointer; white-space: nowrap; transition: all 0.15s;
}
.tab-pill:hover { color: var(--text); }
.tab-pill.active { background: var(--card); color: var(--primary); font-weight: 600; box-shadow: 0 1px 3px rgba(0,0,0,0.06); }
.tab-panel { display: none; }
.tab-panel.active { display: block; }
</style>
@endsection

@section('content')
<div class="page-header">
    <div><h2>Broker</h2></div>
    <div style="display:flex; gap:0.5rem;">
        <a href="{{ route('brokers.edit', $broker) }}" class="btn btn-outline">Editar</a>
        <a href="{{ route('brokers.index') }}" class="btn btn-outline">Volver</a>
    </div>
</div>

<div class="profile-header">
    <div class="profile-top">
        @if($broker->photo)
            <img src="{{ asset('storage/' . $broker->photo) }}" class="profile-avatar" alt="">
        @else
            <div class="profile-avatar-ph">{{ strtoupper(substr($broker->name, 0, 1)) }}</div>
        @endif
        <div>
            <div class="profile-name">{{ $broker->name }}</div>
            @if($broker->company)
                <div class="profile-company">{{ $broker->company->name }}</div>
            @elseif($broker->company_name)
                <div class="profile-company">{{ $broker->company_name }}</div>
            @endif
            <div class="profile-meta">
                @if($broker->email)<span>&#9993; {{ $broker->email }}</span>@endif
                @if($broker->phone)<span>&#128222; {{ $broker->phone }}</span>@endif
                @if($broker->license_number)<span>&#128196; {{ $broker->license_number }}</span>@endif
                @if($broker->commission_rate)<span>&#128176; {{ $broker->commission_rate }}%</span>@endif
                @if($broker->status === 'active')
                    <span class="badge badge-green">Activo</span>
                @else
                    <span class="badge badge-red">Inactivo</span>
                @endif
            </div>
            @if($broker->specialty)
                <div style="font-size:0.78rem; color:var(--text-muted); margin-top:0.25rem;">Especialidad: {{ $broker->specialty }}</div>
            @endif
        </div>
    </div>
</div>

<div class="stat-row">
    <div class="stat-mini">
        <div class="stat-mini-value">{{ $broker->clients_count }}</div>
        <div class="stat-mini-label">Clientes</div>
    </div>
    <div class="stat-mini">
        <div class="stat-mini-value">{{ $broker->properties_count }}</div>
        <div class="stat-mini-label">Propiedades</div>
    </div>
    <div class="stat-mini">
        <div class="stat-mini-value">{{ $broker->operations_count }}</div>
        <div class="stat-mini-label">Operaciones</div>
    </div>
    <div class="stat-mini">
        <div class="stat-mini-value" style="color:var(--success);">${{ number_format($totalCommission, 0) }}</div>
        <div class="stat-mini-label">Comision Total</div>
    </div>
</div>

<div class="tab-pills">
    <button class="tab-pill active" onclick="switchTab('operations', this)">Operaciones</button>
    <button class="tab-pill" onclick="switchTab('commissions', this)">Comisiones</button>
    <button class="tab-pill" onclick="switchTab('clients', this)">Clientes</button>
</div>

<div class="tab-panel active" data-tab="operations">
    <div class="card">
        <div class="card-body" style="padding:0;">
            <div class="table-wrap">
                <table class="data-table">
                    <thead><tr><th>Tipo</th><th>Propiedad</th><th>Etapa</th><th>Monto</th><th>Fecha</th></tr></thead>
                    <tbody>
                        @forelse($broker->operations as $op)
                        <tr>
                            <td><span class="badge badge-blue">{{ ucfirst($op->type) }}</span></td>
                            <td style="font-size:0.85rem;">
                                @if($op->property)
                                    <a href="{{ route('properties.show', $op->property) }}" style="color:var(--primary);">{{ Str::limit($op->property->title, 30) }}</a>
                                @else —
                                @endif
                            </td>
                            <td style="font-size:0.82rem;">{{ ucfirst(str_replace('_', ' ', $op->stage)) }}</td>
                            <td style="font-size:0.85rem; font-weight:500;">${{ number_format($op->amount, 0) }}</td>
                            <td style="font-size:0.82rem; color:var(--text-muted);">{{ $op->created_at->format('d/m/Y') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted" style="padding:1.5rem;">Sin operaciones.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="tab-panel" data-tab="commissions">
    <div class="card">
        <div class="card-body" style="padding:0;">
            <div class="table-wrap">
                <table class="data-table">
                    <thead><tr><th>Monto</th><th>Porcentaje</th><th>Estado</th><th>Fecha</th></tr></thead>
                    <tbody>
                        @forelse($broker->commissions as $comm)
                        <tr>
                            <td style="font-weight:500;">${{ number_format($comm->amount, 0) }}</td>
                            <td style="font-size:0.85rem;">{{ $comm->percentage }}%</td>
                            <td>
                                @if($comm->status === 'paid')<span class="badge badge-green">Pagado</span>
                                @elseif($comm->status === 'approved')<span class="badge badge-blue">Aprobado</span>
                                @else<span class="badge badge-yellow">Pendiente</span>@endif
                            </td>
                            <td style="font-size:0.82rem; color:var(--text-muted);">{{ $comm->created_at->format('d/m/Y') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted" style="padding:1.5rem;">Sin comisiones.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="tab-panel" data-tab="clients">
    <div class="card">
        <div class="card-body" style="padding:0;">
            <div class="table-wrap">
                <table class="data-table">
                    <thead><tr><th>Nombre</th><th>Email</th><th>Telefono</th><th>Temperatura</th></tr></thead>
                    <tbody>
                        @forelse($broker->clients as $client)
                        <tr>
                            <td><a href="{{ route('clients.show', $client) }}" style="font-weight:500; color:var(--text);">{{ $client->name }}</a></td>
                            <td style="font-size:0.85rem; color:var(--text-muted);">{{ $client->email ?: '—' }}</td>
                            <td style="font-size:0.85rem; color:var(--text-muted);">{{ $client->phone ?: '—' }}</td>
                            <td>
                                @if($client->lead_temperature === 'caliente')<span class="badge badge-red">Caliente</span>
                                @elseif($client->lead_temperature === 'tibio')<span class="badge badge-yellow">Tibio</span>
                                @else<span class="badge badge-blue">Frio</span>@endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted" style="padding:1.5rem;">Sin clientes asignados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@if($broker->bio)
<div class="card" style="margin-top:1.25rem;">
    <div class="card-header"><h3 style="margin:0; font-size:0.95rem;">Bio</h3></div>
    <div class="card-body" style="font-size:0.85rem; color:var(--text-muted);">{{ $broker->bio }}</div>
</div>
@endif
@endsection

@section('scripts')
<script>
function switchTab(name, btn) {
    document.querySelectorAll('.tab-panel').forEach(function(p) { p.classList.toggle('active', p.dataset.tab === name); });
    document.querySelectorAll('.tab-pill').forEach(function(b) { b.classList.remove('active'); });
    btn.classList.add('active');
}
</script>
@endsection
