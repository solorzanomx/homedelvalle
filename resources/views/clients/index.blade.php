@extends('layouts.app-sidebar')
@section('title', 'Clientes')

@section('styles')
<style>
/* ===== VIEW TOGGLE ===== */
.view-toggle { display: flex; gap: 2px; background: var(--bg); border-radius: var(--radius); padding: 3px; }
.view-toggle .vt-btn {
    padding: 0.35rem 0.6rem; border: none; background: none; cursor: pointer; border-radius: 6px;
    font-size: 14px; color: var(--text-muted); transition: all 0.15s; line-height: 1;
}
.view-toggle .vt-btn.active { background: var(--card); color: var(--text); box-shadow: 0 1px 3px rgba(0,0,0,0.08); }

/* ===== TEMP PILLS ===== */
.temp-pills { display: flex; gap: 0.5rem; margin-bottom: 1.25rem; overflow-x: auto; padding-bottom: 2px; }
.temp-pill {
    display: flex; align-items: center; gap: 0.4rem; padding: 0.45rem 0.9rem; border-radius: 20px;
    font-size: 0.78rem; font-weight: 500; border: 1px solid var(--border); background: var(--card);
    color: var(--text-muted); text-decoration: none; white-space: nowrap; transition: all 0.15s;
}
.temp-pill:hover { border-color: var(--primary); color: var(--text); }
.temp-pill.active { background: var(--primary); color: #fff; border-color: var(--primary); }
.temp-pill .pill-count { font-size: 0.7rem; background: rgba(0,0,0,0.08); padding: 1px 6px; border-radius: 10px; }
.temp-pill.active .pill-count { background: rgba(255,255,255,0.25); }

/* ===== FILTER BAR ===== */
.filter-bar {
    background: var(--card); border: 1px solid var(--border); border-radius: var(--radius);
    margin-bottom: 1.25rem; overflow: hidden;
}
.filter-bar-toggle {
    display: flex; align-items: center; gap: 0.5rem; padding: 0.7rem 1.25rem; cursor: pointer;
    font-size: 0.82rem; font-weight: 500; color: var(--text-muted); user-select: none;
}
.filter-bar-toggle:hover { color: var(--text); }
.filter-bar-body { padding: 0 1.25rem 1rem; display: none; }
.filter-bar-body.show { display: block; }
.filter-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(170px, 1fr)); gap: 0.75rem; align-items: end; }
.filter-actions { display: flex; gap: 0.5rem; margin-top: 0.75rem; }

/* ===== CLIENT CARDS ===== */
.client-cards { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; }
@media (max-width: 1200px) { .client-cards { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 640px) { .client-cards { grid-template-columns: 1fr; } }

.cli-card {
    background: var(--card); border: 1px solid var(--border); border-radius: 10px;
    overflow: hidden; transition: box-shadow 0.2s, transform 0.15s; cursor: pointer;
}
.cli-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.08); transform: translateY(-1px); }

.cli-card-temp { height: 3px; }
.temp-hot { background: #ef4444; }
.temp-warm { background: #f59e0b; }
.temp-cold { background: #3b82f6; }

.cli-card-body { padding: 1rem; }
.cli-card-top { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.6rem; }
.cli-avatar {
    width: 42px; height: 42px; border-radius: 50%; flex-shrink: 0; overflow: hidden;
    background: var(--primary); display: flex; align-items: center; justify-content: center;
    color: #fff; font-weight: 600; font-size: 0.9rem;
}
.cli-avatar img { width: 100%; height: 100%; object-fit: cover; }
.cli-name { font-weight: 600; font-size: 0.9rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.cli-email { font-size: 0.78rem; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

.cli-card-meta { display: flex; flex-wrap: wrap; gap: 0.4rem; margin-bottom: 0.6rem; }

.cli-card-info { font-size: 0.78rem; color: var(--text-muted); }

.cli-card-actions {
    display: flex; gap: 0.4rem; padding: 0.6rem 1rem; border-top: 1px solid var(--border);
}
.cli-card-actions .btn { flex: 1; justify-content: center; font-size: 0.78rem; padding: 0.35rem 0.5rem; }
.btn-whatsapp { color: #25d366 !important; border-color: #25d366 !important; }

/* ===== TABLE AVATAR ===== */
.tbl-avatar {
    width: 32px; height: 32px; border-radius: 50%; overflow: hidden; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 0.75rem; color: #fff;
}
.tbl-avatar img { width: 100%; height: 100%; object-fit: cover; }
.temp-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; }

/* ===== BADGE EXTRA ===== */
.badge-orange { background: #fff7ed; color: #c2410c; }
.badge-purple { background: #f3f0ff; color: #6d28d9; }

/* ===== FAB ===== */
.cli-fab {
    display: none; position: fixed; bottom: 80px; right: 16px; z-index: 91;
    width: 52px; height: 52px; border-radius: 50%; border: none;
    background: var(--primary); color: #fff; font-size: 26px; font-weight: 300;
    box-shadow: 0 4px 14px rgba(102,126,234,0.4);
    align-items: center; justify-content: center; cursor: pointer; text-decoration: none;
}
@media (max-width: 768px) {
    .cli-fab { display: flex; }
    .filter-grid { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 480px) { .filter-grid { grid-template-columns: 1fr; } }
</style>
@endsection

@section('content')
@php
    $tempLabels = ['caliente' => 'Caliente', 'tibio' => 'Tibio', 'frio' => 'Frio'];
    $tempColors = ['caliente' => 'hot', 'tibio' => 'warm', 'frio' => 'cold'];
    $prioLabels = ['alta' => 'Alta', 'media' => 'Media', 'baja' => 'Baja'];
    $prioBadges = ['alta' => 'badge-red', 'media' => 'badge-yellow', 'baja' => 'badge-blue'];
@endphp

<div class="page-header">
    <div>
        <h2>Clientes</h2>
        <p class="text-muted">{{ $clients->total() }} cliente{{ $clients->total() !== 1 ? 's' : '' }}</p>
    </div>
    <div style="display:flex; gap:0.75rem; align-items:center;">
        <div class="view-toggle">
            <button type="button" class="vt-btn" id="btnCards" onclick="setView('cards')" title="Tarjetas">&#9638;</button>
            <button type="button" class="vt-btn" id="btnList" onclick="setView('list')" title="Lista">&#9776;</button>
        </div>
        <a href="{{ route('clients.create') }}" class="btn btn-primary" style="white-space:nowrap;">+ Nuevo</a>
    </div>
</div>

{{-- Temperature pills --}}
<div class="temp-pills">
    <a href="{{ route('clients.index', request()->except('lead_temperature')) }}" class="temp-pill {{ !request('lead_temperature') ? 'active' : '' }}">
        Todos <span class="pill-count">{{ $clients->total() }}</span>
    </a>
    <a href="{{ route('clients.index', array_merge(request()->except('lead_temperature'), ['lead_temperature' => 'caliente'])) }}" class="temp-pill {{ request('lead_temperature') === 'caliente' ? 'active' : '' }}" style="{{ request('lead_temperature') === 'caliente' ? '' : 'border-color:#fca5a5; color:#ef4444;' }}">
        &#128293; Calientes
    </a>
    <a href="{{ route('clients.index', array_merge(request()->except('lead_temperature'), ['lead_temperature' => 'tibio'])) }}" class="temp-pill {{ request('lead_temperature') === 'tibio' ? 'active' : '' }}" style="{{ request('lead_temperature') === 'tibio' ? '' : 'border-color:#fde68a; color:#f59e0b;' }}">
        &#9728; Tibios
    </a>
    <a href="{{ route('clients.index', array_merge(request()->except('lead_temperature'), ['lead_temperature' => 'frio'])) }}" class="temp-pill {{ request('lead_temperature') === 'frio' ? 'active' : '' }}" style="{{ request('lead_temperature') === 'frio' ? '' : 'border-color:#93c5fd; color:#3b82f6;' }}">
        &#10052; Frios
    </a>
</div>

{{-- Filters --}}
<div class="filter-bar">
    <div class="filter-bar-toggle" onclick="this.nextElementSibling.classList.toggle('show'); this.querySelector('.fchev').style.transform = this.nextElementSibling.classList.contains('show') ? 'rotate(180deg)' : '';">
        &#128269; Filtros {{ request()->hasAny(['search','property_type','broker_id','marketing_channel_id','priority','budget_min','budget_max']) ? '(activos)' : '' }}
        <span class="fchev" style="font-size:0.7rem; transition:transform 0.2s;">&#9660;</span>
    </div>
    <form method="GET" action="{{ route('clients.index') }}" class="filter-bar-body {{ request()->hasAny(['search','property_type','broker_id','marketing_channel_id','priority','budget_min','budget_max']) ? 'show' : '' }}">
        @if(request('lead_temperature'))<input type="hidden" name="lead_temperature" value="{{ request('lead_temperature') }}">@endif
        <div class="filter-grid">
            <div class="form-group" style="margin:0;"><label class="form-label">Buscar</label><input type="text" name="search" class="form-input" value="{{ request('search') }}" placeholder="Nombre, email, tel..."></div>
            <div class="form-group" style="margin:0;"><label class="form-label">Interes</label>
                <select name="property_type" class="form-select"><option value="">Todos</option>@foreach(['House'=>'Casa','Apartment'=>'Depto','Land'=>'Terreno','Office'=>'Oficina','Commercial'=>'Comercial'] as $v => $l)<option value="{{ $v }}" {{ request('property_type') === $v ? 'selected' : '' }}>{{ $l }}</option>@endforeach</select>
            </div>
            <div class="form-group" style="margin:0;"><label class="form-label">Prioridad</label>
                <select name="priority" class="form-select"><option value="">Todas</option>@foreach($prioLabels as $v => $l)<option value="{{ $v }}" {{ request('priority') === $v ? 'selected' : '' }}>{{ $l }}</option>@endforeach</select>
            </div>
            <div class="form-group" style="margin:0;"><label class="form-label">Broker</label>
                <select name="broker_id" class="form-select"><option value="">Todos</option>@foreach($brokers as $b)<option value="{{ $b->id }}" {{ request('broker_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>@endforeach</select>
            </div>
            <div class="form-group" style="margin:0;"><label class="form-label">Canal</label>
                <select name="marketing_channel_id" class="form-select"><option value="">Todos</option>@foreach($channels as $ch)<option value="{{ $ch->id }}" {{ request('marketing_channel_id') == $ch->id ? 'selected' : '' }}>{{ $ch->name }}</option>@endforeach</select>
            </div>
            <div class="form-group" style="margin:0;"><label class="form-label">Budget min</label><input type="number" name="budget_min" class="form-input" value="{{ request('budget_min') }}" placeholder="0"></div>
            <div class="form-group" style="margin:0;"><label class="form-label">Budget max</label><input type="number" name="budget_max" class="form-input" value="{{ request('budget_max') }}" placeholder="Sin limite"></div>
        </div>
        <div class="filter-actions">
            <button type="submit" class="btn btn-primary btn-sm">Aplicar</button>
            <a href="{{ route('clients.index') }}" class="btn btn-outline btn-sm">Limpiar</a>
        </div>
    </form>
</div>

{{-- ===== CARD VIEW ===== --}}
<div id="viewCards" style="display:none;">
    @if($clients->count())
    <div class="client-cards">
        @foreach($clients as $client)
        <div class="cli-card" onclick="window.location='{{ route('clients.show', $client) }}'">
            <div class="cli-card-temp temp-{{ $tempColors[$client->lead_temperature] ?? 'cold' }}"></div>
            <div class="cli-card-body">
                <div class="cli-card-top">
                    <div class="cli-avatar" style="background: {{ $client->lead_temperature === 'caliente' ? '#ef4444' : ($client->lead_temperature === 'tibio' ? '#f59e0b' : 'var(--primary)') }};">
                        @if($client->photo)
                            <img src="{{ asset('storage/' . $client->photo) }}" alt="">
                        @else
                            {{ strtoupper(substr($client->name, 0, 1)) }}
                        @endif
                    </div>
                    <div style="overflow:hidden; flex:1;">
                        <div class="cli-name">{{ $client->name }}</div>
                        <div class="cli-email">{{ $client->email ?? '—' }}</div>
                    </div>
                </div>
                <div class="cli-card-meta">
                    @if($client->lead_temperature)
                        <span class="badge {{ $client->lead_temperature === 'caliente' ? 'badge-red' : ($client->lead_temperature === 'tibio' ? 'badge-yellow' : 'badge-blue') }}">
                            {{ $tempLabels[$client->lead_temperature] ?? $client->lead_temperature }}
                        </span>
                    @endif
                    @if($client->priority)
                        <span class="badge {{ $prioBadges[$client->priority] ?? 'badge-blue' }}">{{ $prioLabels[$client->priority] ?? $client->priority }}</span>
                    @endif
                    @if($client->property_type)
                        <span class="badge badge-purple">{{ $client->property_type }}</span>
                    @endif
                </div>
                <div class="cli-card-info">
                    @if($client->city){{ $client->city }}@endif
                    @if($client->budget_max) &middot; ${{ number_format($client->budget_max, 0) }}@endif
                    @if($client->broker) &middot; {{ $client->broker->name }}@endif
                </div>
            </div>
            <div class="cli-card-actions" onclick="event.stopPropagation();">
                @if($client->whatsapp ?? $client->phone)
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $client->whatsapp ?? $client->phone) }}" target="_blank" class="btn btn-sm btn-outline btn-whatsapp">WhatsApp</a>
                @endif
                @if($client->phone)
                    <a href="tel:{{ $client->phone }}" class="btn btn-sm btn-outline">Llamar</a>
                @endif
                <a href="{{ route('clients.show', $client) }}" class="btn btn-sm btn-outline">Ver</a>
            </div>
        </div>
        @endforeach
    </div>
    @if($clients->hasPages())
    <div style="margin-top:1.25rem; text-align:center;">{{ $clients->links() }}</div>
    @endif
    @else
    <div class="card"><div style="text-align:center; padding:4rem 2rem; color:var(--text-muted);"><div style="font-size:3rem; margin-bottom:0.75rem; opacity:0.4;">&#9823;</div><div style="font-size:0.95rem; margin-bottom:1rem;">No hay clientes registrados</div><a href="{{ route('clients.create') }}" class="btn btn-primary">+ Agregar primer cliente</a></div></div>
    @endif
</div>

{{-- ===== TABLE VIEW ===== --}}
<div id="viewList">
    <div class="card">
        <div class="card-body" style="padding:0;">
            <div class="table-wrap">
                <table class="data-table">
                    <thead><tr><th></th><th>Cliente</th><th>Contacto</th><th>Temp.</th><th>Prioridad</th><th>Interes</th><th>Budget</th><th></th></tr></thead>
                    <tbody>
                        @forelse($clients as $client)
                        <tr style="cursor:pointer;" onclick="window.location='{{ route('clients.show', $client) }}'">
                            <td style="width:48px;">
                                <div class="tbl-avatar" style="background: {{ $client->lead_temperature === 'caliente' ? '#ef4444' : ($client->lead_temperature === 'tibio' ? '#f59e0b' : 'var(--primary)') }};">
                                    @if($client->photo)<img src="{{ asset('storage/' . $client->photo) }}" alt="">@else{{ strtoupper(substr($client->name, 0, 1)) }}@endif
                                </div>
                            </td>
                            <td>
                                <div style="font-weight:500;">{{ $client->name }}</div>
                                <div style="font-size:0.78rem; color:var(--text-muted);">{{ $client->email ?? '—' }}</div>
                            </td>
                            <td style="font-size:0.82rem;" onclick="event.stopPropagation();">
                                @if($client->phone)
                                    <div>{{ $client->phone }}</div>
                                    <div style="display:flex; gap:0.25rem; margin-top:2px;">
                                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $client->whatsapp ?? $client->phone) }}" target="_blank" style="font-size:0.72rem; color:#25d366;">WA</a>
                                        <a href="tel:{{ $client->phone }}" style="font-size:0.72rem; color:var(--primary);">Tel</a>
                                    </div>
                                @else — @endif
                            </td>
                            <td>
                                @if($client->lead_temperature)
                                    <span class="temp-dot" style="background:{{ $client->lead_temperature === 'caliente' ? '#ef4444' : ($client->lead_temperature === 'tibio' ? '#f59e0b' : '#3b82f6') }};" title="{{ $tempLabels[$client->lead_temperature] ?? '' }}"></span>
                                    <span style="font-size:0.78rem;">{{ $tempLabels[$client->lead_temperature] ?? '' }}</span>
                                @else — @endif
                            </td>
                            <td>
                                @if($client->priority)<span class="badge {{ $prioBadges[$client->priority] ?? 'badge-blue' }}">{{ $prioLabels[$client->priority] ?? '' }}</span>@else — @endif
                            </td>
                            <td style="font-size:0.82rem;">{{ $client->property_type ?? '—' }}</td>
                            <td style="font-size:0.82rem; white-space:nowrap;">
                                @if($client->budget_max)${{ number_format($client->budget_max, 0) }}@else — @endif
                            </td>
                            <td onclick="event.stopPropagation();"><div class="action-btns"><a href="{{ route('clients.edit', $client) }}" class="btn btn-sm btn-outline">Editar</a></div></td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="text-center text-muted" style="padding:3rem;">No hay clientes. <a href="{{ route('clients.create') }}" style="color:var(--primary); font-weight:500;">+ Agregar primero</a></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($clients->hasPages())<div style="padding:0.75rem 1.25rem; border-top:1px solid var(--border);">{{ $clients->links() }}</div>@endif
        </div>
    </div>
</div>

<a href="{{ route('clients.create') }}" class="cli-fab">+</a>
@endsection

@section('scripts')
<script>
function setView(mode) {
    var cards = document.getElementById('viewCards'), list = document.getElementById('viewList');
    var btnC = document.getElementById('btnCards'), btnL = document.getElementById('btnList');
    if (mode === 'cards') {
        cards.style.display = ''; list.style.display = 'none';
        btnC.classList.add('active'); btnL.classList.remove('active');
    } else {
        cards.style.display = 'none'; list.style.display = '';
        btnL.classList.add('active'); btnC.classList.remove('active');
    }
    try { localStorage.setItem('clients_view', mode); } catch(e) {}
}
(function() { var s = 'cards'; try { s = localStorage.getItem('clients_view') || 'cards'; } catch(e) {} setView(s); })();
</script>
@endsection
