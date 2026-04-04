@extends('layouts.app-sidebar')
@section('title', $broker->name . ' - Broker Externo')

@section('styles')
<style>
/* ===== Profile Header ===== */
.profile-header {
    background: var(--card); border: 1px solid var(--border); border-radius: 16px;
    padding: 0; margin-bottom: 1.25rem; overflow: hidden;
}
.profile-cover {
    height: 48px; position: relative;
}
.profile-head {
    display: flex; align-items: flex-end; gap: 1.25rem; padding: 0 2rem 1.5rem;
    margin-top: -48px; position: relative; z-index: 1;
}
.profile-avatar {
    width: 96px; height: 96px; border-radius: 50%; background: var(--card);
    border: 4px solid var(--card); display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 2rem; color: #fff; overflow: hidden;
    flex-shrink: 0; box-shadow: 0 4px 16px rgba(0,0,0,0.1);
}
.profile-avatar img { width: 100%; height: 100%; object-fit: cover; }
.profile-meta { flex: 1; padding-bottom: 0.2rem; }
.profile-name { font-size: 1.25rem; font-weight: 700; line-height: 1.3; }
.profile-subtitle { font-size: 0.82rem; color: var(--text-muted); }
.profile-badges { display: flex; gap: 0.35rem; margin-top: 0.35rem; flex-wrap: wrap; }
.profile-actions { display: flex; gap: 0.5rem; align-items: flex-end; padding-bottom: 0.3rem; }

/* ===== Tabs ===== */
.p-tabs {
    display: flex; gap: 0; border-bottom: 1px solid var(--border); margin: 0 2rem;
}
.p-tab {
    padding: 0.7rem 1.25rem; font-size: 0.82rem; font-weight: 500;
    border: none; background: none; color: var(--text-muted); cursor: pointer;
    position: relative; transition: color 0.15s;
}
.p-tab:hover { color: var(--text); }
.p-tab.active { color: var(--primary); font-weight: 600; }
.p-tab.active::after {
    content: ''; position: absolute; bottom: -1px; left: 0; right: 0;
    height: 2px; background: var(--primary); border-radius: 2px 2px 0 0;
}

/* ===== Panels ===== */
.p-panel { display: none; padding: 1.5rem 2rem; animation: panelIn 0.2s ease; }
.p-panel.active { display: block; }
@keyframes panelIn { from { opacity: 0; transform: translateY(4px); } to { opacity: 1; } }

.p-section-title {
    font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em;
    color: var(--text-muted); margin: 1.5rem 0 0.75rem; padding-bottom: 0.4rem;
    border-bottom: 1px solid var(--border);
}
.p-section-title:first-child { margin-top: 0; }

/* ===== Info Rows ===== */
.info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0 2rem; }
.info-item { padding: 0.65rem 0; border-bottom: 1px solid var(--border); }
.info-item:last-child, .info-item:nth-last-child(2):nth-child(odd) + .info-item { border-bottom: none; }
.info-label { font-size: 0.72rem; color: var(--text-muted); margin-bottom: 0.15rem; }
.info-value { font-size: 0.85rem; font-weight: 500; }
.info-value a { color: var(--primary); text-decoration: none; }
.info-value a:hover { text-decoration: underline; }
.info-full { grid-column: 1 / -1; }

/* ===== Quick Actions ===== */
.quick-actions {
    display: flex; gap: 0.5rem; padding: 1rem 2rem; border-top: 1px solid var(--border);
    background: var(--bg); border-radius: 0 0 16px 16px;
}
.quick-actions .btn { flex: unset; }

/* ===== Responsive ===== */
@media (max-width: 768px) {
    .profile-head { flex-direction: column; align-items: center; text-align: center; padding: 0 1rem 1.25rem; }
    .profile-actions { justify-content: center; }
    .p-tabs { margin: 0 1rem; overflow-x: auto; }
    .p-panel { padding: 1.25rem 1rem; }
    .info-grid { grid-template-columns: 1fr; }
    .quick-actions { padding: 0.75rem 1rem; flex-wrap: wrap; }
}
</style>
@endsection

@section('content')
@php
    $avatarColors = ['#3B82C4','#1E3A5F','#f093fb','#4facfe','#43e97b','#fa709a'];
@endphp

<div style="margin-bottom:1rem; display:flex; align-items:center; gap:0.5rem;">
    <a href="{{ route('brokers.index') }}" style="font-size:0.82rem; color:var(--text-muted);">&#8592; Brokers Externos</a>
    <span style="color:var(--text-muted); font-size:0.72rem;">/</span>
    <span style="font-size:0.82rem; color:var(--text);">{{ $broker->name }}</span>
</div>

<div class="profile-header">
    {{-- Cover + Avatar --}}
    <div class="profile-cover"></div>
    <div class="profile-head">
        <div class="profile-avatar" style="background:{{ $avatarColors[$broker->id % count($avatarColors)] }};">
            @if($broker->photo)
                <img src="{{ asset('storage/' . $broker->photo) }}" alt="">
            @else
                {{ strtoupper(substr($broker->name, 0, 1)) }}
            @endif
        </div>
        <div class="profile-meta">
            <div class="profile-name">{{ $broker->name }}</div>
            <div class="profile-subtitle">{{ $broker->email }}</div>
            <div class="profile-badges">
                @if($broker->company)
                    <span class="badge" style="background:rgba(59,130,196,0.1); color:var(--primary); font-size:0.72rem;">{{ $broker->company->name }}</span>
                @elseif($broker->company_name)
                    <span class="badge" style="background:rgba(148,163,184,0.15); color:#94a3b8; font-size:0.72rem;">{{ $broker->company_name }}</span>
                @endif
                @if($broker->status === 'active')
                    <span class="badge badge-green" style="font-size:0.68rem;">Activo</span>
                @else
                    <span class="badge badge-red" style="font-size:0.68rem;">Inactivo</span>
                @endif
                @if($broker->commission_rate)
                    <span class="badge" style="background:rgba(234,179,8,0.1); color:#ca8a04; font-size:0.68rem;">{{ $broker->commission_rate }}%</span>
                @endif
            </div>
        </div>
        <div class="profile-actions">
            @if($broker->phone)
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $broker->phone) }}" target="_blank" class="btn btn-sm" style="background:#25d366; color:#fff; border:none;">WhatsApp</a>
            @endif
            <a href="{{ route('brokers.edit', $broker) }}" class="btn btn-sm btn-primary">Editar</a>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="p-tabs">
        <button type="button" class="p-tab active" onclick="showTab('info', this)">Informacion</button>
        <button type="button" class="p-tab" onclick="showTab('operations', this)">Operaciones <span style="font-size:0.68rem; color:var(--text-muted);">{{ $broker->operations_count }}</span></button>
        <button type="button" class="p-tab" onclick="showTab('commissions', this)">Comisiones</button>
        <button type="button" class="p-tab" onclick="showTab('clients', this)">Clientes <span style="font-size:0.68rem; color:var(--text-muted);">{{ $broker->clients_count }}</span></button>
    </div>

    {{-- Tab: Info --}}
    <div class="p-panel active" id="panel-info">
        <div class="p-section-title">Informacion personal</div>
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">Nombre completo</div>
                <div class="info-value">{{ $broker->name }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Email</div>
                <div class="info-value"><a href="mailto:{{ $broker->email }}">{{ $broker->email }}</a></div>
            </div>
            <div class="info-item">
                <div class="info-label">Telefono</div>
                <div class="info-value">{{ $broker->phone ?: '—' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Empresa</div>
                <div class="info-value">
                    @if($broker->company)
                        <a href="{{ route('broker-companies.edit', $broker->company) }}">{{ $broker->company->name }}</a>
                    @elseif($broker->company_name)
                        {{ $broker->company_name }}
                    @else
                        —
                    @endif
                </div>
            </div>
        </div>

        <div class="p-section-title">Datos profesionales</div>
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">Numero de Licencia</div>
                <div class="info-value">{{ $broker->license_number ?: '—' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Comision</div>
                <div class="info-value">{{ $broker->commission_rate ? $broker->commission_rate . '%' : '—' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Especialidad</div>
                <div class="info-value">{{ $broker->specialty ?: '—' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Como lo conocimos</div>
                <div class="info-value">{{ $broker->referral_source ?: '—' }}</div>
            </div>
        </div>

        <div class="p-section-title">Estadisticas</div>
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">Clientes</div>
                <div class="info-value">{{ $broker->clients_count }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Propiedades</div>
                <div class="info-value">{{ $broker->properties_count }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Operaciones</div>
                <div class="info-value">{{ $broker->operations_count }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Comision total</div>
                <div class="info-value" style="color:var(--success);">${{ number_format($totalCommission, 0) }}</div>
            </div>
        </div>

        @if($broker->bio)
        <div class="p-section-title">Bio</div>
        <div class="info-grid">
            <div class="info-item info-full">
                <div class="info-value" style="font-weight:400;">{{ $broker->bio }}</div>
            </div>
        </div>
        @endif

        <div class="p-section-title">Detalles de cuenta</div>
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">Registrado</div>
                <div class="info-value">{{ $broker->created_at->format('d M Y') }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Ultima actualizacion</div>
                <div class="info-value">{{ $broker->updated_at->diffForHumans() }}</div>
            </div>
        </div>
    </div>

    {{-- Tab: Operations --}}
    <div class="p-panel" id="panel-operations">
        <div class="p-section-title">Operaciones</div>
        @forelse($broker->operations as $op)
        <div style="display:flex; align-items:center; gap:0.75rem; padding:0.65rem 0; border-bottom:1px solid var(--border);">
            <span class="badge badge-blue" style="font-size:0.72rem;">{{ ucfirst($op->type) }}</span>
            <div style="flex:1; min-width:0;">
                @if($op->property)
                    <a href="{{ route('properties.show', $op->property) }}" style="color:var(--primary); font-size:0.85rem; font-weight:500;">{{ Str::limit($op->property->title, 35) }}</a>
                @else
                    <span style="font-size:0.85rem; color:var(--text-muted);">—</span>
                @endif
                <div style="font-size:0.72rem; color:var(--text-muted);">{{ ucfirst(str_replace('_', ' ', $op->stage)) }}</div>
            </div>
            <div style="text-align:right;">
                <div style="font-size:0.85rem; font-weight:600;">${{ number_format($op->amount, 0) }}</div>
                <div style="font-size:0.72rem; color:var(--text-muted);">{{ $op->created_at->format('d/m/Y') }}</div>
            </div>
        </div>
        @empty
        <p style="text-align:center; padding:2rem; color:var(--text-muted); font-size:0.85rem;">Sin operaciones registradas.</p>
        @endforelse
    </div>

    {{-- Tab: Commissions --}}
    <div class="p-panel" id="panel-commissions">
        <div class="p-section-title">Comisiones</div>
        @forelse($broker->commissions as $comm)
        <div style="display:flex; align-items:center; gap:0.75rem; padding:0.65rem 0; border-bottom:1px solid var(--border);">
            <div style="flex:1;">
                <div style="font-size:0.85rem; font-weight:600;">${{ number_format($comm->amount, 0) }}</div>
                <div style="font-size:0.72rem; color:var(--text-muted);">{{ $comm->percentage }}%</div>
            </div>
            <div>
                @if($comm->status === 'paid')<span class="badge badge-green" style="font-size:0.72rem;">Pagado</span>
                @elseif($comm->status === 'approved')<span class="badge badge-blue" style="font-size:0.72rem;">Aprobado</span>
                @else<span class="badge badge-yellow" style="font-size:0.72rem;">Pendiente</span>@endif
            </div>
            <div style="font-size:0.72rem; color:var(--text-muted);">{{ $comm->created_at->format('d/m/Y') }}</div>
        </div>
        @empty
        <p style="text-align:center; padding:2rem; color:var(--text-muted); font-size:0.85rem;">Sin comisiones registradas.</p>
        @endforelse
    </div>

    {{-- Tab: Clients --}}
    <div class="p-panel" id="panel-clients">
        <div class="p-section-title">Clientes asignados</div>
        @forelse($broker->clients as $client)
        <div style="display:flex; align-items:center; gap:0.75rem; padding:0.65rem 0; border-bottom:1px solid var(--border);">
            <div style="flex:1; min-width:0;">
                <a href="{{ route('clients.show', $client) }}" style="font-weight:500; font-size:0.85rem; color:var(--text);">{{ $client->name }}</a>
                <div style="font-size:0.72rem; color:var(--text-muted);">{{ $client->email ?: $client->phone ?: '—' }}</div>
            </div>
            <div>
                @if($client->lead_temperature === 'caliente')<span class="badge badge-red" style="font-size:0.68rem;">Caliente</span>
                @elseif($client->lead_temperature === 'tibio')<span class="badge badge-yellow" style="font-size:0.68rem;">Tibio</span>
                @else<span class="badge badge-blue" style="font-size:0.68rem;">Frio</span>@endif
            </div>
        </div>
        @empty
        <p style="text-align:center; padding:2rem; color:var(--text-muted); font-size:0.85rem;">Sin clientes asignados.</p>
        @endforelse
    </div>

    {{-- Footer Actions --}}
    <div class="quick-actions">
        <a href="{{ route('brokers.edit', $broker) }}" class="btn btn-primary btn-sm">Editar perfil</a>
        @if($broker->phone)
            <a href="tel:{{ $broker->phone }}" class="btn btn-outline btn-sm">Llamar</a>
        @endif
        <a href="mailto:{{ $broker->email }}" class="btn btn-outline btn-sm">Enviar email</a>
    </div>
</div>

{{-- Danger Zone --}}
<div style="max-width: 480px; margin-top: 1.5rem;">
    <div style="background:var(--card); border:1px solid #fecaca; border-radius:12px; padding:1.25rem;">
        <div style="font-size:0.82rem; font-weight:600; color:#991b1b; margin-bottom:0.25rem;">Zona de peligro</div>
        <p style="font-size:0.75rem; color:var(--text-muted); margin-bottom:0.75rem;">Eliminar este broker de forma permanente.</p>
        <form method="POST" action="{{ route('brokers.destroy', $broker) }}" onsubmit="return confirm('Seguro que deseas eliminar a {{ $broker->name }}? Esta accion no se puede deshacer.')">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-danger btn-sm">Eliminar broker</button>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
function showTab(name, btn) {
    document.querySelectorAll('.p-panel').forEach(function(p) { p.classList.toggle('active', p.id === 'panel-' + name); });
    document.querySelectorAll('.p-tab').forEach(function(t) { t.classList.remove('active'); });
    btn.classList.add('active');
}
</script>
@endsection
