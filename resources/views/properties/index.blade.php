@extends('layouts.app-sidebar')
@section('title', 'Propiedades')

@section('styles')
<style>
/* ===== VIEW TOGGLE ===== */
.view-toggle { display: flex; gap: 2px; background: var(--bg); border-radius: var(--radius); padding: 3px; }
.view-toggle .vt-btn {
    padding: 0.35rem 0.6rem; border: none; background: none; cursor: pointer; border-radius: 6px;
    font-size: 14px; color: var(--text-muted); transition: all 0.15s; line-height: 1;
}
.view-toggle .vt-btn.active { background: var(--card); color: var(--text); box-shadow: 0 1px 3px rgba(0,0,0,0.08); }

/* ===== QUICK STATS ===== */
.prop-stats {
    display: flex; gap: 1.5rem; margin-bottom: 1.25rem; padding: 0.8rem 1.25rem;
    background: var(--card); border: 1px solid var(--border); border-radius: var(--radius); overflow-x: auto;
}
.prop-stat { display: flex; align-items: center; gap: 0.5rem; white-space: nowrap; }
.prop-stat-val { font-size: 1.1rem; font-weight: 700; }
.prop-stat-lbl { font-size: 0.75rem; color: var(--text-muted); }
.prop-stat-div { width: 1px; background: var(--border); align-self: stretch; }

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

/* ===== PROPERTY CARDS ===== */
.property-cards { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; }
@media (max-width: 1200px) { .property-cards { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 640px) { .property-cards { grid-template-columns: 1fr; } }

.prop-card {
    background: var(--card); border: 1px solid var(--border); border-radius: 10px;
    overflow: hidden; transition: box-shadow 0.2s, transform 0.2s; cursor: pointer; position: relative;
}
.prop-card:hover { box-shadow: 0 6px 20px rgba(0,0,0,0.08); transform: translateY(-2px); }
.prop-card-img {
    position: relative; height: 170px; overflow: hidden;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
}
.prop-card-img img { width: 100%; height: 100%; object-fit: cover; display: block; }
.prop-card-img .placeholder-icon {
    width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;
    color: rgba(255,255,255,0.5); font-size: 2.5rem;
}
.prop-card-badges { position: absolute; top: 8px; left: 8px; display: flex; gap: 4px; flex-wrap: wrap; }
.prop-card-badges .cbadge {
    padding: 2px 8px; font-size: 0.68rem; font-weight: 600; border-radius: 4px; backdrop-filter: blur(8px);
}
.cbadge-type { background: rgba(255,255,255,0.9); color: var(--text); }
.cbadge-op { background: rgba(102,126,234,0.9); color: #fff; }
.cbadge-status-available { background: rgba(16,185,129,0.9); color: #fff; }
.cbadge-status-sold { background: rgba(239,68,68,0.9); color: #fff; }
.cbadge-status-rented { background: rgba(245,158,11,0.9); color: #fff; }
.prop-card-price-tag {
    position: absolute; bottom: 8px; right: 8px; padding: 4px 10px;
    background: rgba(0,0,0,0.7); color: #fff; font-weight: 700; font-size: 0.88rem;
    border-radius: 6px; backdrop-filter: blur(4px);
}
.prop-card-body { padding: 0.8rem 1rem; }
.prop-card-title { font-weight: 600; font-size: 0.9rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-bottom: 0.2rem; }
.prop-card-loc { font-size: 0.78rem; color: var(--text-muted); margin-bottom: 0.6rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.prop-card-features { display: flex; gap: 0.75rem; font-size: 0.75rem; color: var(--text-muted); }
.prop-card-features span { display: flex; align-items: center; gap: 3px; }
.prop-card-footer { display: flex; gap: 0.4rem; padding: 0.6rem 1rem; border-top: 1px solid var(--border); }
.prop-card-footer .btn { flex: 1; justify-content: center; font-size: 0.78rem; padding: 0.35rem 0.5rem; }

/* ===== TABLE ===== */
.thumb-cell img { width: 48px; height: 36px; object-fit: cover; border-radius: 4px; }
.thumb-cell .thumb-placeholder { width: 48px; height: 36px; border-radius: 4px; background: linear-gradient(135deg, var(--primary), var(--primary-dark)); opacity: 0.4; }
.eb-dot { display: inline-block; width: 8px; height: 8px; border-radius: 50%; vertical-align: middle; }

/* ===== EMPTY STATE ===== */
.empty-state { text-align: center; padding: 4rem 2rem; color: var(--text-muted); }
.empty-state-icon { font-size: 3rem; margin-bottom: 0.75rem; opacity: 0.4; }

/* ===== FAB ===== */
.prop-fab {
    display: none; position: fixed; bottom: 80px; right: 16px; z-index: 91;
    width: 52px; height: 52px; border-radius: 50%; border: none;
    background: var(--primary); color: #fff; font-size: 26px; font-weight: 300;
    box-shadow: 0 4px 14px rgba(102,126,234,0.4);
    align-items: center; justify-content: center; cursor: pointer; text-decoration: none;
}
@media (max-width: 768px) {
    .prop-fab { display: flex; }
    .prop-card-img { height: 140px; }
    .filter-grid { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 480px) { .filter-grid { grid-template-columns: 1fr; } }
</style>
@endsection

@section('content')
@php
    $types = ['House'=>'Casa','Apartment'=>'Depto','Land'=>'Terreno','Office'=>'Oficina','Commercial'=>'Comercial','Warehouse'=>'Bodega','Building'=>'Edificio'];
    $opLabels = ['sale'=>'Venta','rental'=>'Renta','temporary_rental'=>'Renta Temporal'];
@endphp

<div class="page-header">
    <div>
        <h2>Propiedades</h2>
        <p class="text-muted">{{ $properties->total() }} propiedad{{ $properties->total() !== 1 ? 'es' : '' }}</p>
    </div>
    <div style="display:flex; gap:0.75rem; align-items:center;">
        <div class="view-toggle">
            <button type="button" class="vt-btn" id="btnCards" onclick="setView('cards')" title="Tarjetas">&#9638;</button>
            <button type="button" class="vt-btn" id="btnList" onclick="setView('list')" title="Lista">&#9776;</button>
        </div>
        <a href="{{ route('properties.create') }}" class="btn btn-primary" style="white-space:nowrap;">+ Nueva</a>
    </div>
</div>

{{-- Quick Stats --}}
<div class="prop-stats">
    <div class="prop-stat">
        <div><div class="prop-stat-val">{{ $properties->total() }}</div><div class="prop-stat-lbl">Total</div></div>
    </div>
    <div class="prop-stat-div"></div>
    <div class="prop-stat">
        <div><div class="prop-stat-val" style="color:var(--success);">{{ $properties->getCollection()->where('status','available')->count() }}</div><div class="prop-stat-lbl">Disponibles</div></div>
    </div>
    <div class="prop-stat-div"></div>
    <div class="prop-stat">
        <div><div class="prop-stat-val" style="color:var(--danger);">{{ $properties->getCollection()->where('status','sold')->count() }}</div><div class="prop-stat-lbl">Vendidas</div></div>
    </div>
    <div class="prop-stat-div"></div>
    <div class="prop-stat">
        <div><div class="prop-stat-val" style="color:#f59e0b;">{{ $properties->getCollection()->where('status','rented')->count() }}</div><div class="prop-stat-lbl">Rentadas</div></div>
    </div>
</div>

{{-- Filters --}}
<div class="filter-bar">
    <div class="filter-bar-toggle" onclick="this.nextElementSibling.classList.toggle('show'); this.querySelector('.fchev').style.transform = this.nextElementSibling.classList.contains('show') ? 'rotate(180deg)' : '';">
        &#128269; Filtros {{ request()->hasAny(['search','property_type','status','operation_type','broker_id','price_min','price_max']) ? '(activos)' : '' }}
        <span class="fchev" style="font-size:0.7rem; transition:transform 0.2s;">&#9660;</span>
    </div>
    <form method="GET" action="{{ route('properties.index') }}" class="filter-bar-body {{ request()->hasAny(['search','property_type','status','operation_type','broker_id','price_min','price_max']) ? 'show' : '' }}">
        <div class="filter-grid">
            <div class="form-group" style="margin:0;"><label class="form-label">Buscar</label><input type="text" name="search" class="form-input" value="{{ request('search') }}" placeholder="Titulo, ciudad..."></div>
            <div class="form-group" style="margin:0;"><label class="form-label">Tipo</label>
                <select name="property_type" class="form-select"><option value="">Todos</option>@foreach($types as $val => $label)<option value="{{ $val }}" {{ request('property_type') === $val ? 'selected' : '' }}>{{ $label }}</option>@endforeach</select>
            </div>
            <div class="form-group" style="margin:0;"><label class="form-label">Estado</label>
                <select name="status" class="form-select"><option value="">Todos</option><option value="available" {{ request('status') === 'available' ? 'selected' : '' }}>Disponible</option><option value="sold" {{ request('status') === 'sold' ? 'selected' : '' }}>Vendido</option><option value="rented" {{ request('status') === 'rented' ? 'selected' : '' }}>Rentado</option></select>
            </div>
            <div class="form-group" style="margin:0;"><label class="form-label">Operacion</label>
                <select name="operation_type" class="form-select"><option value="">Todas</option><option value="sale" {{ request('operation_type') === 'sale' ? 'selected' : '' }}>Venta</option><option value="rental" {{ request('operation_type') === 'rental' ? 'selected' : '' }}>Renta</option><option value="temporary_rental" {{ request('operation_type') === 'temporary_rental' ? 'selected' : '' }}>Temporal</option></select>
            </div>
            <div class="form-group" style="margin:0;"><label class="form-label">Broker</label>
                <select name="broker_id" class="form-select"><option value="">Todos</option>@foreach($brokers as $broker)<option value="{{ $broker->id }}" {{ request('broker_id') == $broker->id ? 'selected' : '' }}>{{ $broker->name }}</option>@endforeach</select>
            </div>
            <div class="form-group" style="margin:0;"><label class="form-label">Precio min</label><input type="number" name="price_min" class="form-input" value="{{ request('price_min') }}" placeholder="0"></div>
            <div class="form-group" style="margin:0;"><label class="form-label">Precio max</label><input type="number" name="price_max" class="form-input" value="{{ request('price_max') }}" placeholder="Sin limite"></div>
        </div>
        <div class="filter-actions">
            <button type="submit" class="btn btn-primary btn-sm">Aplicar</button>
            <a href="{{ route('properties.index') }}" class="btn btn-outline btn-sm">Limpiar</a>
        </div>
    </form>
</div>

{{-- ===== CARD VIEW ===== --}}
<div id="viewCards" style="display:none;">
    @if($properties->count())
    <div class="property-cards">
        @foreach($properties as $property)
        <div class="prop-card" onclick="window.location='{{ route('properties.show', $property) }}'">
            <div class="prop-card-img">
                @if($property->photo)
                    <img src="{{ asset('storage/' . $property->photo) }}" alt="{{ $property->title }}">
                @else
                    <div class="placeholder-icon">&#8962;</div>
                @endif
                <div class="prop-card-badges">
                    <span class="cbadge cbadge-type">{{ $types[$property->property_type] ?? $property->property_type ?? '—' }}</span>
                    @if($property->operation_type)
                        <span class="cbadge cbadge-op">{{ $opLabels[$property->operation_type] ?? $property->operation_type }}</span>
                    @endif
                    <span class="cbadge cbadge-status-{{ $property->status ?? 'available' }}">
                        {{ $property->status === 'sold' ? 'Vendido' : ($property->status === 'rented' ? 'Rentado' : 'Disponible') }}
                    </span>
                </div>
                <div class="prop-card-price-tag">${{ number_format($property->price, 0) }} {{ $property->currency ?? 'MXN' }}</div>
            </div>
            <div class="prop-card-body">
                <div class="prop-card-title">{{ $property->title }}</div>
                <div class="prop-card-loc">{{ implode(', ', array_filter([$property->colony, $property->city])) ?: 'Sin ubicacion' }}</div>
                <div class="prop-card-features">
                    @if($property->bedrooms !== null)<span>&#128716; {{ $property->bedrooms }}</span>@endif
                    @if($property->bathrooms !== null)<span>&#128703; {{ $property->bathrooms }}</span>@endif
                    @if($property->area !== null)<span>&#9633; {{ $property->area }}m&sup2;</span>@endif
                    @if($property->parking !== null)<span>&#127359; {{ $property->parking }}</span>@endif
                </div>
            </div>
            <div class="prop-card-footer" onclick="event.stopPropagation();">
                <a href="{{ route('properties.show', $property) }}" class="btn btn-sm btn-outline">Ver</a>
                <a href="{{ route('properties.edit', $property) }}" class="btn btn-sm btn-outline">Editar</a>
            </div>
        </div>
        @endforeach
    </div>
    @if($properties->hasPages())
    <div style="margin-top:1.25rem; text-align:center;">{{ $properties->links() }}</div>
    @endif
    @else
    <div class="card"><div class="empty-state"><div class="empty-state-icon">&#8962;</div><div style="font-size:0.95rem; margin-bottom:1rem;">No hay propiedades registradas</div><a href="{{ route('properties.create') }}" class="btn btn-primary">+ Agregar primera propiedad</a></div></div>
    @endif
</div>

{{-- ===== TABLE VIEW ===== --}}
<div id="viewList">
    <div class="card">
        <div class="card-body" style="padding:0;">
            <div class="table-wrap">
                <table class="data-table">
                    <thead><tr><th></th><th>Propiedad</th><th>Precio</th><th>Tipo</th><th>Estado</th><th>Caracteristicas</th><th>EB</th><th></th></tr></thead>
                    <tbody>
                        @forelse($properties as $property)
                        <tr style="cursor:pointer;" onclick="window.location='{{ route('properties.show', $property) }}'">
                            <td class="thumb-cell" style="width:56px;">
                                @if($property->photo)<img src="{{ asset('storage/' . $property->photo) }}" alt="">@else<div class="thumb-placeholder"></div>@endif
                            </td>
                            <td>
                                <div style="font-weight:500;">{{ $property->title }}</div>
                                <div style="font-size:0.78rem; color:var(--text-muted);">{{ implode(', ', array_filter([$property->colony, $property->city])) ?: '—' }}</div>
                            </td>
                            <td style="font-weight:600; white-space:nowrap;">${{ number_format($property->price, 0) }} {{ $property->currency ?? 'MXN' }}</td>
                            <td>
                                <span class="badge badge-blue">{{ $types[$property->property_type] ?? $property->property_type ?? '—' }}</span>
                                @if($property->operation_type)<span class="badge" style="background:#f3f0ff; color:#6d28d9;">{{ $opLabels[$property->operation_type] ?? '' }}</span>@endif
                            </td>
                            <td>
                                @if($property->status === 'sold')<span class="badge badge-red">Vendido</span>@elseif($property->status === 'rented')<span class="badge badge-yellow">Rentado</span>@else<span class="badge badge-green">Disponible</span>@endif
                            </td>
                            <td style="font-size:0.78rem; color:var(--text-muted); white-space:nowrap;">
                                @if($property->bedrooms !== null){{ $property->bedrooms }}rec @endif
                                @if($property->bathrooms !== null){{ $property->bathrooms }}ban @endif
                                @if($property->area){{ $property->area }}m² @endif
                            </td>
                            <td>
                                @if($property->isPublishedToEasyBroker())<span class="eb-dot" style="background:var(--success);" title="Publicada"></span>@elseif($property->hasEasyBrokerId())<span class="eb-dot" style="background:#eab308;" title="Despublicada"></span>@else<span class="eb-dot" style="background:var(--border);" title="No publicada"></span>@endif
                            </td>
                            <td onclick="event.stopPropagation();"><div class="action-btns"><a href="{{ route('properties.edit', $property) }}" class="btn btn-sm btn-outline">Editar</a></div></td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="text-center text-muted" style="padding:3rem;">No hay propiedades. <a href="{{ route('properties.create') }}" style="color:var(--primary); font-weight:500;">+ Agregar primera</a></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($properties->hasPages())<div style="padding:0.75rem 1.25rem; border-top:1px solid var(--border);">{{ $properties->links() }}</div>@endif
        </div>
    </div>
</div>

<a href="{{ route('properties.create') }}" class="prop-fab">+</a>
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
    try { localStorage.setItem('properties_view', mode); } catch(e) {}
}
(function() { var s = 'cards'; try { s = localStorage.getItem('properties_view') || 'cards'; } catch(e) {} setView(s); })();
</script>
@endsection
