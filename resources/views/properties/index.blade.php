@extends('layouts.app-sidebar')
@section('title', 'Propiedades')

@section('styles')
<style>
/* ===== Stats (same pattern as users) ===== */
.p-stats { display: flex; flex-direction: row; flex-wrap: nowrap; gap: 0.75rem; margin-bottom: 1.5rem; }
.p-stat {
    flex: 1; min-width: 0; background: var(--card); border: 1px solid var(--border);
    border-radius: 12px; padding: 1rem 1.25rem; display: flex; align-items: center; gap: 0.75rem;
}
.p-stat-icon {
    width: 42px; height: 42px; border-radius: 10px; display: flex; align-items: center;
    justify-content: center; font-size: 1.1rem; flex-shrink: 0;
}
.p-stat-val { font-size: 1.4rem; font-weight: 700; line-height: 1; }
.p-stat-label { font-size: 0.72rem; color: var(--text-muted); margin-top: 0.1rem; }

/* ===== Toolbar ===== */
.p-toolbar {
    display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.25rem; flex-wrap: wrap;
}
.p-search { flex: 1; min-width: 200px; position: relative; }
.p-search input {
    width: 100%; padding: 0.55rem 0.75rem 0.55rem 2.2rem; border: 1px solid var(--border);
    border-radius: 8px; font-size: 0.82rem; background: var(--card); color: var(--text);
    outline: none; transition: border-color 0.15s;
}
.p-search input:focus { border-color: var(--primary); }
.p-search-icon {
    position: absolute; left: 0.7rem; top: 50%; transform: translateY(-50%);
    color: var(--text-muted); font-size: 0.9rem; pointer-events: none;
}

/* ===== Status Tabs ===== */
.p-tabs {
    display: flex; gap: 2px; background: var(--bg); border-radius: 8px; padding: 3px;
    border: 1px solid var(--border); overflow-x: auto;
}
.p-tab {
    padding: 0.4rem 0.85rem; border-radius: 6px; font-size: 0.78rem; font-weight: 500;
    border: none; background: transparent; color: var(--text-muted); cursor: pointer;
    white-space: nowrap; transition: all 0.15s;
}
.p-tab:hover { color: var(--text); }
.p-tab.active { background: var(--card); color: var(--primary); font-weight: 600; box-shadow: 0 1px 3px rgba(0,0,0,0.06); }
.p-tab .p-tab-count {
    font-size: 0.65rem; background: var(--bg); padding: 0 5px; border-radius: 8px;
    margin-left: 3px; font-weight: 600; color: var(--text-muted);
}
.p-tab.active .p-tab-count { background: rgba(59,130,196,0.12); color: var(--primary); }

/* ===== Filters Toggle ===== */
.p-filters-toggle {
    padding: 0.45rem 0.85rem; border-radius: 8px; font-size: 0.78rem; font-weight: 500;
    border: 1px solid var(--border); background: var(--card); color: var(--text-muted);
    cursor: pointer; white-space: nowrap; transition: all 0.15s;
}
.p-filters-toggle:hover { border-color: var(--primary); color: var(--primary); }
.p-filters-toggle.active { border-color: var(--primary); color: var(--primary); background: rgba(59,130,196,0.04); }
.p-filters-panel {
    display: none; background: var(--card); border: 1px solid var(--border); border-radius: 10px;
    padding: 1rem 1.25rem; margin-bottom: 1.25rem;
}
.p-filters-panel.show { display: block; }
.p-filter-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 0.75rem; align-items: end; }

/* ===== Property Grid ===== */
.p-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.85rem; }
.p-card {
    background: var(--card); border: 1px solid var(--border); border-radius: 12px;
    overflow: hidden; transition: all 0.2s; position: relative;
}
.p-card:hover { border-color: var(--primary); box-shadow: 0 4px 20px rgba(0,0,0,0.06); transform: translateY(-1px); }
.p-card-img {
    display: block; position: relative; height: 180px; overflow: hidden;
    background: linear-gradient(135deg, #3B82C4, #1E3A5F); text-decoration: none;
}
.p-card-img img { width: 100%; height: 100%; object-fit: cover; display: block; transition: transform 0.3s; }
.p-card:hover .p-card-img img { transform: scale(1.03); }
.p-card-placeholder {
    width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;
    color: rgba(255,255,255,0.4); font-size: 2.5rem;
}
.p-card-badges { position: absolute; top: 8px; left: 8px; display: flex; gap: 4px; flex-wrap: wrap; }
.p-badge { padding: 2px 8px; font-size: 0.66rem; font-weight: 600; border-radius: 4px; backdrop-filter: blur(8px); }
.p-badge-type { background: rgba(255,255,255,0.92); color: var(--text); }
.p-badge-op { background: rgba(59,130,196,0.9); color: #fff; }
.p-card-status {
    position: absolute; top: 8px; right: 8px; padding: 2px 8px;
    font-size: 0.66rem; font-weight: 600; border-radius: 4px; backdrop-filter: blur(8px);
}
.p-status-available { background: rgba(16,185,129,0.9); color: #fff; }
.p-status-sold { background: rgba(239,68,68,0.9); color: #fff; }
.p-status-rented { background: rgba(245,158,11,0.9); color: #fff; }
.p-card-price {
    position: absolute; bottom: 8px; right: 8px; padding: 3px 10px;
    background: rgba(0,0,0,0.7); color: #fff; font-weight: 700; font-size: 0.85rem;
    border-radius: 6px; backdrop-filter: blur(4px);
}
.p-card-body { padding: 0.75rem 1rem 0.5rem; }
.p-card-title {
    display: block; font-weight: 600; font-size: 0.88rem; color: var(--text); text-decoration: none;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-bottom: 0.15rem;
}
.p-card-title:hover { color: var(--primary); }
.p-card-loc { font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.5rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.p-card-specs { display: flex; gap: 0.65rem; font-size: 0.73rem; color: var(--text-muted); margin-bottom: 0.4rem; }
.p-card-specs span { display: flex; align-items: center; gap: 3px; }
.p-card-owner {
    font-size: 0.7rem; color: var(--text-muted); display: flex; align-items: center; gap: 4px;
    padding-top: 0.4rem; border-top: 1px solid var(--border);
}
.p-owner-dot { width: 6px; height: 6px; border-radius: 50%; background: var(--primary); flex-shrink: 0; }
.p-card-actions {
    display: flex; gap: 0.35rem; padding: 0.5rem 0.75rem; border-top: 1px solid var(--border);
}

/* ===== Empty ===== */
.p-empty {
    text-align: center; padding: 4rem 2rem; color: var(--text-muted);
    background: var(--card); border: 1px solid var(--border); border-radius: 12px;
}
.p-empty-icon { font-size: 3rem; opacity: 0.2; margin-bottom: 0.75rem; }

/* ===== Share Modal ===== */
.p-share-overlay {
    display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 999;
    align-items: center; justify-content: center;
}
.p-share-overlay.show { display: flex; }
.p-share-modal {
    background: var(--card); border-radius: 14px; padding: 1.5rem; width: 360px; max-width: 90vw;
    box-shadow: 0 20px 60px rgba(0,0,0,0.15);
}
.p-share-modal h4 { font-size: 0.95rem; font-weight: 600; margin-bottom: 1rem; }
.p-share-btn {
    display: flex; align-items: center; gap: 0.75rem; width: 100%; padding: 0.65rem 0.85rem;
    border: 1px solid var(--border); border-radius: 8px; background: var(--card);
    cursor: pointer; font-size: 0.82rem; transition: all 0.15s; margin-bottom: 0.5rem;
    color: var(--text); text-decoration: none;
}
.p-share-btn:hover { border-color: var(--primary); background: rgba(59,130,196,0.04); }
.p-share-icon { font-size: 1.1rem; width: 24px; text-align: center; }

/* ===== FAB ===== */
.p-fab {
    display: none; position: fixed; bottom: 80px; right: 16px; z-index: 91;
    width: 52px; height: 52px; border-radius: 50%; border: none;
    background: var(--primary); color: #fff; font-size: 26px; font-weight: 300;
    box-shadow: 0 4px 14px rgba(59,130,196,0.4);
    align-items: center; justify-content: center; cursor: pointer; text-decoration: none;
}

@media (max-width: 1200px) { .p-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 768px) {
    .p-grid { grid-template-columns: 1fr; }
    .p-stats { flex-wrap: wrap; }
    .p-stat { flex: 1 1 calc(50% - 0.75rem); }
    .p-fab { display: flex; }
    .p-toolbar { flex-direction: column; align-items: stretch; }
    .p-card-img { height: 160px; }
}
@media (max-width: 480px) {
    .p-filter-grid { grid-template-columns: 1fr; }
}
</style>
@endsection

@section('content')
@php
    $types = ['House'=>'Casa','Apartment'=>'Depto','Land'=>'Terreno','Office'=>'Oficina','Commercial'=>'Comercial','Warehouse'=>'Bodega','Building'=>'Edificio'];
    $opLabels = ['sale'=>'Venta','rental'=>'Renta','temporary_rental'=>'Renta Temporal'];
@endphp

{{-- Stats --}}
<div class="p-stats" style="display:flex; flex-direction:row; flex-wrap:nowrap; gap:0.75rem; margin-bottom:1.5rem;">
    <div class="p-stat" style="flex:1;">
        <div class="p-stat-icon" style="background:rgba(59,130,196,0.1); color:var(--primary);">&#8962;</div>
        <div><div class="p-stat-val">{{ $stats['total'] }}</div><div class="p-stat-label">Total</div></div>
    </div>
    <div class="p-stat" style="flex:1;">
        <div class="p-stat-icon" style="background:rgba(16,185,129,0.1); color:#10b981;">&#10003;</div>
        <div><div class="p-stat-val">{{ $stats['available'] }}</div><div class="p-stat-label">Disponibles</div></div>
    </div>
    <div class="p-stat" style="flex:1;">
        <div class="p-stat-icon" style="background:rgba(239,68,68,0.1); color:#ef4444;">&#9679;</div>
        <div><div class="p-stat-val">{{ $stats['sold'] }}</div><div class="p-stat-label">Vendidas</div></div>
    </div>
    <div class="p-stat" style="flex:1;">
        <div class="p-stat-icon" style="background:rgba(245,158,11,0.1); color:#f59e0b;">&#128196;</div>
        <div><div class="p-stat-val">{{ $stats['rented'] }}</div><div class="p-stat-label">Rentadas</div></div>
    </div>
</div>

{{-- Toolbar --}}
<div class="p-toolbar">
    <div class="p-search">
        <span class="p-search-icon">&#128269;</span>
        <input type="text" id="propSearch" placeholder="Buscar por titulo, ciudad, colonia..." value="{{ request('search') }}" autocomplete="off">
    </div>
    <div class="p-tabs" id="statusTabs">
        <button class="p-tab {{ !request('status') ? 'active' : '' }}" data-status="">Todas <span class="p-tab-count">{{ $stats['total'] }}</span></button>
        <button class="p-tab {{ request('status') === 'available' ? 'active' : '' }}" data-status="available">Disponibles <span class="p-tab-count">{{ $stats['available'] }}</span></button>
        <button class="p-tab {{ request('status') === 'sold' ? 'active' : '' }}" data-status="sold">Vendidas <span class="p-tab-count">{{ $stats['sold'] }}</span></button>
        <button class="p-tab {{ request('status') === 'rented' ? 'active' : '' }}" data-status="rented">Rentadas <span class="p-tab-count">{{ $stats['rented'] }}</span></button>
    </div>
    <button type="button" class="p-filters-toggle {{ request()->hasAny(['property_type','operation_type','broker_id','price_min','price_max']) ? 'active' : '' }}" onclick="toggleFilters()">
        &#9776; Filtros
    </button>
    <a href="{{ route('properties.create') }}" class="btn btn-primary" style="white-space:nowrap; padding:0.5rem 1rem;">+ Nueva</a>
</div>

{{-- Advanced Filters --}}
<div class="p-filters-panel {{ request()->hasAny(['property_type','operation_type','broker_id','price_min','price_max']) ? 'show' : '' }}" id="filtersPanel">
    <div class="p-filter-grid">
        <div class="form-group" style="margin:0;"><label class="form-label" style="font-size:0.72rem;">Tipo</label>
            <select id="fType" class="form-select" onchange="loadProperties()"><option value="">Todos</option>@foreach($types as $val => $label)<option value="{{ $val }}" {{ request('property_type') === $val ? 'selected' : '' }}>{{ $label }}</option>@endforeach</select>
        </div>
        <div class="form-group" style="margin:0;"><label class="form-label" style="font-size:0.72rem;">Operacion</label>
            <select id="fOp" class="form-select" onchange="loadProperties()"><option value="">Todas</option><option value="sale" {{ request('operation_type') === 'sale' ? 'selected' : '' }}>Venta</option><option value="rental" {{ request('operation_type') === 'rental' ? 'selected' : '' }}>Renta</option><option value="temporary_rental" {{ request('operation_type') === 'temporary_rental' ? 'selected' : '' }}>Temporal</option></select>
        </div>
        <div class="form-group" style="margin:0;"><label class="form-label" style="font-size:0.72rem;">Broker</label>
            <select id="fBroker" class="form-select" onchange="loadProperties()"><option value="">Todos</option>@foreach($brokers as $broker)<option value="{{ $broker->id }}" {{ request('broker_id') == $broker->id ? 'selected' : '' }}>{{ $broker->name }}</option>@endforeach</select>
        </div>
        <div class="form-group" style="margin:0;"><label class="form-label" style="font-size:0.72rem;">Precio min</label>
            <input type="number" id="fPriceMin" class="form-input" value="{{ request('price_min') }}" placeholder="0" onchange="loadProperties()">
        </div>
        <div class="form-group" style="margin:0;"><label class="form-label" style="font-size:0.72rem;">Precio max</label>
            <input type="number" id="fPriceMax" class="form-input" value="{{ request('price_max') }}" placeholder="Sin limite" onchange="loadProperties()">
        </div>
        <div class="form-group" style="margin:0; display:flex; align-items:flex-end;">
            <button type="button" class="btn btn-outline btn-sm" onclick="clearFilters()" style="width:100%;">Limpiar</button>
        </div>
    </div>
</div>

{{-- Property Grid --}}
<div id="propGrid">
    @include('properties._grid', ['properties' => $properties])
</div>

<a href="{{ route('properties.create') }}" class="p-fab">+</a>

{{-- Share Modal --}}
<div class="p-share-overlay" id="shareOverlay" onclick="if(event.target===this) closeShare()">
    <div class="p-share-modal">
        <h4>Compartir propiedad</h4>
        <a id="shareWhatsApp" href="#" target="_blank" class="p-share-btn">
            <span class="p-share-icon">&#128172;</span> Enviar por WhatsApp
        </a>
        <a id="shareEmail" href="#" class="p-share-btn">
            <span class="p-share-icon">&#9993;</span> Enviar por correo
        </a>
        <button type="button" class="p-share-btn" onclick="copyLink()">
            <span class="p-share-icon">&#128279;</span> <span id="copyText">Copiar enlace</span>
        </button>
        <button type="button" class="btn btn-outline" style="width:100%; margin-top:0.5rem;" onclick="closeShare()">Cerrar</button>
    </div>
</div>
@endsection

@section('scripts')
<script>
var searchTimer, currentStatus = '{{ request('status', '') }}';
var shareUrl = '';

// Status tab click
document.querySelectorAll('.p-tab').forEach(function(tab) {
    tab.addEventListener('click', function() {
        document.querySelectorAll('.p-tab').forEach(function(t) { t.classList.remove('active'); });
        this.classList.add('active');
        currentStatus = this.dataset.status;
        loadProperties();
    });
});

// Search with debounce
document.getElementById('propSearch').addEventListener('input', function() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(function() { loadProperties(); }, 350);
});

function loadProperties() {
    var params = new URLSearchParams();
    var search = document.getElementById('propSearch').value;
    if (search) params.set('search', search);
    if (currentStatus) params.set('status', currentStatus);

    var fType = document.getElementById('fType').value;
    var fOp = document.getElementById('fOp').value;
    var fBroker = document.getElementById('fBroker').value;
    var fPriceMin = document.getElementById('fPriceMin').value;
    var fPriceMax = document.getElementById('fPriceMax').value;

    if (fType) params.set('property_type', fType);
    if (fOp) params.set('operation_type', fOp);
    if (fBroker) params.set('broker_id', fBroker);
    if (fPriceMin) params.set('price_min', fPriceMin);
    if (fPriceMax) params.set('price_max', fPriceMax);

    var url = '{{ route("properties.index") }}?' + params.toString();
    document.getElementById('propGrid').style.opacity = '0.5';

    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
    .then(function(r) { return r.text(); })
    .then(function(html) {
        document.getElementById('propGrid').innerHTML = html;
        document.getElementById('propGrid').style.opacity = '1';
    });
}

function toggleFilters() {
    var panel = document.getElementById('filtersPanel');
    var btn = document.querySelector('.p-filters-toggle');
    panel.classList.toggle('show');
    btn.classList.toggle('active');
}

function clearFilters() {
    document.getElementById('fType').value = '';
    document.getElementById('fOp').value = '';
    document.getElementById('fBroker').value = '';
    document.getElementById('fPriceMin').value = '';
    document.getElementById('fPriceMax').value = '';
    loadProperties();
}

function shareProperty(id, title, url) {
    shareUrl = url;
    var msg = encodeURIComponent(title + '\n' + url);
    document.getElementById('shareWhatsApp').href = 'https://wa.me/?text=' + msg;
    document.getElementById('shareEmail').href = 'mailto:?subject=' + encodeURIComponent(title) + '&body=' + msg;
    document.getElementById('shareOverlay').classList.add('show');
}

function closeShare() { document.getElementById('shareOverlay').classList.remove('show'); }

function copyLink() {
    navigator.clipboard.writeText(shareUrl).then(function() {
        document.getElementById('copyText').textContent = 'Copiado!';
        setTimeout(function() { document.getElementById('copyText').textContent = 'Copiar enlace'; }, 2000);
    });
}
</script>
@endsection
